#!/usr/bin/env bash
#
# Copy uploaded files from your laptop to Hostinger (NOT in git).
# Run from project root on your local machine:
#   bash scripts/sync-storage-to-hostinger.sh
#
# Override defaults:
#   HOSTINGER_SSH=u906168952@de-fra-web1806 \
#   HOSTINGER_REMOTE=~/domains/lineupaligner.com/public_html \
#   bash scripts/sync-storage-to-hostinger.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOCAL="${ROOT}/storage/app/public"
SSH_HOST="${HOSTINGER_SSH:-u906168952@de-fra-web1806}"
REMOTE="${HOSTINGER_REMOTE:-~/domains/lineupaligner.com/public_html}"
REMOTE_STORAGE="${REMOTE}/storage/app/public"

DIRS=(settings profiles website)

echo "==> Sync storage uploads to Hostinger"
echo "    Local:  ${LOCAL}"
echo "    Remote: ${SSH_HOST}:${REMOTE_STORAGE}"
echo ""

for dir in "${DIRS[@]}"; do
  if [[ ! -d "${LOCAL}/${dir}" ]]; then
    echo "Skip ${dir}/ (folder missing locally)"
    continue
  fi

  count="$(find "${LOCAL}/${dir}" -type f ! -name '.gitignore' 2>/dev/null | wc -l | tr -d ' ')"
  if [[ "${count}" == "0" ]]; then
    echo "Skip ${dir}/ (no files locally)"
    continue
  fi

  echo "Upload ${dir}/ (${count} files)..."
  ssh "${SSH_HOST}" "mkdir -p ${REMOTE_STORAGE}/${dir}"
  rsync -avz --progress "${LOCAL}/${dir}/" "${SSH_HOST}:${REMOTE_STORAGE}/${dir}/"
done

echo ""
echo "==> Fix permissions on server"
ssh "${SSH_HOST}" "cd ${REMOTE} && bash scripts/hostinger-link.sh && php artisan view:clear && php artisan config:cache"
echo ""
echo "Done. Hard-refresh https://lineupaligner.com"
