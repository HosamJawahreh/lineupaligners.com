#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PUBLIC="${ROOT}/public"

mkdir -p "${ROOT}/storage/app/public/profiles"
mkdir -p "${ROOT}/storage/app/public/settings"
mkdir -p "${ROOT}/storage/app/public/website/images"
mkdir -p "${ROOT}/storage/app/public/website/videos"

cat > "${ROOT}/storage/app/public/.htaccess" <<'EOF'
<IfModule mod_authz_core.c>
    Require all granted
</IfModule>
<IfModule !mod_authz_core.c>
    Order allow,deny
    Allow from all
</IfModule>

Options -Indexes
EOF
echo "OK  storage/app/public/.htaccess"

chmod 755 "${ROOT}/storage/app/public" "${ROOT}/storage/app/public/profiles" \
    "${ROOT}/storage/app/public/settings" "${ROOT}/storage/app/public/website" 2>/dev/null || true
chmod 644 "${ROOT}/storage/app/public/.htaccess" 2>/dev/null || true

link_path() {
    local name="$1"
    local relative_target="$2"
    local link="${PUBLIC}/${name}"

    if [[ -L "${link}" ]] && [[ "$(readlink "${link}")" == "${relative_target}" ]]; then
        echo "OK  ${link} -> ${relative_target}"
        return 0
    fi

    if [[ -e "${link}" && ! -L "${link}" ]]; then
        echo "ERROR ${link} exists and is not a symlink."
        echo "      Remove it manually, then re-run this script."
        return 1
    fi

    if [[ -L "${link}" ]]; then
        rm "${link}"
    fi

    (cd "${PUBLIC}" && ln -s "${relative_target}" "${name}")
    echo "Linked ${link} -> ${relative_target}"
}

link_path "storage" "../storage/app/public"
link_path "assets" "../assets"

echo "Symlinks ready. Test URLs:"
echo "  /assets/smiliz/images/logo.svg"
echo "  /storage/settings/"
