# PHOTOIMPORT_ARCHITECTURE

This document describes how `local_yucardphoto` imports photos from the external Oracle database into Moodle, including why the importer uses single-BLOB fetches and how 500-size batching works in one task run.

## 1) Scope and implementation location

- Plugin task class: `public/local/yucardphoto/classes/task/import_yucard_photos.php`
- Task type: Moodle scheduled task (`\core\task\scheduled_task`)
- Source system: external Oracle view (for example, `envision.vw_yk_eclass_photos`)
- Destination:
  - Binary image files stored in Moodle file storage (filesystem-backed file API)
  - Metadata/reference stored in `mdl_local_yucardphoto`

## 2) High-level flow

Each task run performs these phases:

1. Read plugin config (DB type, TNS, schema/table, column names, batch size)
2. Validate reachability (quick TCP test to Oracle host/port)
3. Open one Oracle connection for the run
4. Fetch metadata only (SISID + modified date), no image bytes
5. Load Moodle user lookup (`user.idnumber -> firstname/lastname`)
6. Compute delta (`needsupdate`) by comparing source modified date to local stored date
7. Process `needsupdate` in batches of 500
8. For each SISID in a batch:
   - Skip if no matching Moodle user
   - Fetch one BLOB for that SISID only
   - Detect MIME type
   - Store file in Moodle file area
   - Insert/update `mdl_local_yucardphoto`
9. Close Oracle connection and print run summary

## 3) Why metadata-first and single-BLOB fetches

### Metadata-first strategy

The importer first queries only light metadata:

- `SISID`
- `PHOTOMODIFIEDDATE` (as ISO text using `TO_CHAR`)

This avoids transferring image bytes for records that did not change.

### Single-BLOB per SISID strategy

The importer does **not** open one large cursor containing all BLOBs. Instead, it runs one targeted query per SISID:

- `SELECT PHOTO FROM ... WHERE SISID = :sisid`

Rationale:

- Keeps each BLOB statement short-lived
- Reduces risk of long-cursor/session timeouts (for example ORA-03114 on long-running deferred BLOB reads)
- Limits memory pressure to one image at a time
- Improves resilience (one bad row does not fail all BLOB reads)

In short: this is a streaming-like model at row level rather than bulk-loading all image payloads.

## 4) 500-size batching in the same task run

After delta detection, the task chunks work using:

- config key: `local_yucardphoto/yucard_batch_size`
- default: `500`
- processing primitive: `array_chunk($needsupdate, $batchsize, true)`

Meaning:

- If 7,400 photos need updates and batch size is 500, the same task run executes 15 batches (14 full + 1 partial)
- The task does **not** stop after first 500 unless an error interrupts execution
- Batch logs show start/end and timing for each chunk

## 5) Batch and timing telemetry

The task logs:

- Number of metadata rows read
- Number of Moodle users loaded for SISID lookup
- Number unchanged vs needs update
- Batch progress:
  - `starting batch X/Y`
  - `completed batch X/Y in Ns`
- Per-batch counters (inserted, updated, skipped, no-user, errors)
- Final run summary with:
  - batch size
  - number of batches
  - inserted/updated/unchanged/skipped/no-user/errors
  - total duration

This makes long initial loads observable and easier to tune.

## 6) Update rules and idempotency

For each SISID:

- If no local row exists: insert new metadata row
- If local row exists and source modified timestamp changed: update local row
- If timestamps match: skip (unchanged)

This makes repeated runs idempotent for unchanged photos.

## 7) User matching behavior

A photo is imported only when a Moodle user exists with matching `user.idnumber` equal to source `SISID`.

If user is missing:

- importer logs info
- increments `nousers` and `skipped`
- does not fetch/store that photo payload

This prevents creating orphan photo records without local user context.

## 8) Error handling behavior

- Connection-level failures (unreachable host, failed connect): run exits early with trace output
- Per-row failures (bad blob, unrecognized MIME, exception while storing/updating): row is skipped and counted, run continues
- Next cron run can retry remaining rows via normal delta logic

## 9) Performance characteristics

### Initial load

- Heavy in total bytes because many records are new/changed
- Controlled by metadata-first delta + batch chunking + one-BLOB-at-a-time fetch

### Subsequent nightly loads

- Typically light because most rows become unchanged
- Delta step quickly filters to only new/changed photos

## 10) Operational knobs

- `yucard_batch_size` (default 500): throughput vs runtime/log granularity
- Oracle reachability and credential settings
- Source table/column config (`SISID`, `PHOTO`, `PHOTOMODIFIEDDATE`)

## 11) Example conceptual pseudo-flow

```text
connect Oracle
fetch metadata map
build needsupdate map
chunks = chunk(needsupdate, 500)
for each chunk:
  log start chunk
  for each sisid in chunk:
    if no Moodle user: skip
    blob = fetch single blob by sisid
    if invalid blob/mime: skip
    write file to Moodle file API
    upsert local_yucardphoto row
  log chunk timing/counters
close Oracle
log final summary
```

## 12) Notes for troubleshooting

If expected photos are not importing, check in this order:

1. Oracle connectivity from runtime container
2. `yucard_*` plugin config values
3. Matching `user.idnumber` values for SISIDs
4. MIME validity of returned BLOBs
5. Batch logs to identify where skips/errors occur
6. Moodle cache/theme purge is not required for file import itself (only for UI/CSS changes)

