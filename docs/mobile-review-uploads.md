# Local mobile review uploads

Start the development backend using `./scripts/serve-local.ps1` (port 8000 by default). The previous `./scripts/serve-mobile.ps1` command still works.
This starts a loopback-only PHP process with `upload_max_filesize=30M`, `post_max_size=96M`, and `max_file_uploads=20`.

Laravel keeps its per-review limits of 6 JPG/JPEG/PNG/WebP photos at 5 MiB each and 2 MP4 videos at 30 MiB each. The largest permitted payload is 90 MiB plus multipart overhead; PHP's request limit must exceed that. A server started before a php.ini change keeps its previous limits until restarted. `php -d ... artisan serve` alone does not reliably pass those overrides to its child PHP server, so this launcher starts the actual server process directly.

No credentials or global PHP settings are changed. No migration is required for this fix. The existing reviews.media column must already be installed; applying missing migrations requires owner approval.

For deployed PHP/FPM or a reverse proxy, verify the actual serving process and upstream body limits before making a configuration change. Restart/reload that service when changing its configuration. Do not infer serving-process limits from CLI PHP output alone.

The picker retains in-memory attachment bytes. The multipart client uses indexed images/videos fields and content-derived MIME metadata, while Laravel validates the files again. Successful responses must include the saved review ID and rating. Buyer/product locking makes retries return the existing review without uploading duplicate media, and failed transactions clean up only their own files.
