#!/usr/bin/env bash
# Start Laravel dev server with 128M upload limits for 3D scans.
cd "$(dirname "$0")"
exec php -d upload_max_filesize=128M -d post_max_size=132M -d max_execution_time=300 artisan serve "$@"
