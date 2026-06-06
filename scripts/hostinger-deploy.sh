#!/usr/bin/env bash
#
# Run once after every deploy on Hostinger (from project root / public_html).
#   bash scripts/hostinger-deploy.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

echo "==> LineUp Hostinger deploy"
echo "    Project: ${ROOT}"
echo ""

bash "${ROOT}/scripts/hostinger-link.sh"
echo ""

php artisan lineup:deploy-hostinger

echo ""
echo "==> Done. Hard-refresh the site in your browser."
