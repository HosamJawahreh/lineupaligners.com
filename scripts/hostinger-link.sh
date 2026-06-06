#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PUBLIC="${ROOT}/public"

mkdir -p "${ROOT}/storage/app/public"

link_path() {
    local name="$1"
    local relative_target="$2"
    local link="${PUBLIC}/${name}"

    if [[ -L "$link" ]] && [[ "$(readlink -f "$link")" == "$(readlink -f "${PUBLIC}/${relative_target}")" ]] || [[ -L "$link" ]] && [[ "$(readlink "${PUBLIC}/${name}")" == "${relative_target}" ]]; then
        echo "OK  ${link}"
        return 0
    fi

    if [[ -e "$link" ]]; then
        echo "Skip ${link} (exists). Remove it first if you need to recreate the link."
        return 0
    fi

    (cd "${PUBLIC}" && ln -s "${relative_target}" "${name}")
    echo "Linked ${link} -> ${relative_target}"
}

link_path "storage" "../storage/app/public"
link_path "assets" "../assets"

echo "Done. Test in browser:"
echo "  /assets/images/logo.svg"
echo "  /storage/"
