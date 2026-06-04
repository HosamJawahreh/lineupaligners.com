#!/usr/bin/env bash
# Download original Oreo Hospital theme assets from the official demo.
set -euo pipefail

BASE="https://www.wrraptheme.com/templates/oreo/hospital/html"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ASSETS="$ROOT/assets"

download() {
  local path="$1"
  local dest="$ASSETS/${path#assets/}"
  mkdir -p "$(dirname "$dest")"
  if [[ -f "$dest" && -s "$dest" ]]; then
    local size
    size=$(wc -c < "$dest")
    if [[ "$dest" == *.jpg && "$size" -gt 1000 ]]; then
      return 0
    fi
    if [[ "$dest" == *.png && "$size" -gt 1000 ]]; then
      return 0
    fi
    if [[ "$dest" == *.svg && "$size" -gt 500 ]]; then
      return 0
    fi
  fi
  echo "Downloading $path"
  curl -fsSL "$BASE/$path" -o "$dest" || echo "FAILED: $path"
}

# Core images list from HTML templates
while IFS= read -r path; do
  download "$path"
done <<'PATHS'
assets/images/logo.svg
assets/images/login.jpg
assets/images/profile_av.jpg
assets/images/image1.jpg
assets/images/image2.jpg
assets/images/image3.jpg
assets/images/image4.jpg
assets/images/image5.jpg
assets/images/image6.jpg
assets/images/image7.jpg
assets/images/image8.jpg
assets/images/xs/avatar1.jpg
assets/images/xs/avatar2.jpg
assets/images/xs/avatar3.jpg
assets/images/xs/avatar4.jpg
assets/images/xs/avatar5.jpg
assets/images/xs/avatar6.jpg
assets/images/xs/avatar7.jpg
assets/images/xs/avatar8.jpg
assets/images/xs/avatar9.jpg
assets/images/xs/avatar10.jpg
assets/images/sm/avatar1.jpg
assets/images/sm/avatar2.jpg
assets/images/sm/avatar3.jpg
assets/images/sm/avatar4.jpg
assets/images/sm/avatar5.jpg
assets/images/sm/avatar6.jpg
assets/images/lg/avatar1.jpg
assets/images/lg/avatar2.jpg
assets/images/doctors/member1.png
assets/images/doctors/member2.png
assets/images/doctors/member3.png
assets/images/doctors/member4.png
assets/images/doctors/member5.png
assets/images/doctors/member6.png
assets/images/doctors/member7.png
assets/images/doctors/member8.png
assets/images/blog/1.jpg
assets/images/blog/2.jpg
assets/images/blog/3.jpg
assets/images/blog/4.jpg
assets/images/blog/05-img.jpg
assets/images/blog/06-img.jpg
assets/images/blog/07-img.jpg
assets/images/blog/08-img.jpg
assets/images/blog/09-img.jpg
assets/images/blog/10-img.jpg
assets/images/blog/11-img.jpg
assets/images/blog/12-img.jpg
assets/images/blog/13-img.jpg
assets/images/blog/blog-page-1.jpg
assets/images/blog/blog-page-2.jpg
assets/images/blog/blog-page-3.jpg
assets/images/blog/blog-page-4.jpg
assets/images/image-gallery/1.jpg
assets/images/image-gallery/2.jpg
assets/images/image-gallery/3.jpg
assets/images/image-gallery/4.jpg
assets/images/image-gallery/5.jpg
assets/images/image-gallery/6.jpg
assets/images/image-gallery/7.jpg
assets/images/image-gallery/8.jpg
assets/images/image-gallery/9.jpg
assets/images/image-gallery/10.jpg
assets/images/image-gallery/11.jpg
assets/images/image-gallery/12.jpg
assets/images/image-gallery/13.jpg
assets/images/image-gallery/14.jpg
assets/images/image-gallery/15.jpg
assets/images/image-gallery/thumb/thumb-1.jpg
assets/images/image-gallery/thumb/thumb-2.jpg
assets/images/image-gallery/thumb/thumb-3.jpg
assets/images/image-gallery/thumb/thumb-4.jpg
assets/images/image-gallery/thumb/thumb-5.jpg
assets/images/image-gallery/thumb/thumb-6.jpg
assets/images/image-gallery/thumb/thumb-7.jpg
assets/images/image-gallery/thumb/thumb-8.jpg
assets/images/image-gallery/thumb/thumb-9.jpg
assets/images/image-gallery/thumb/thumb-10.jpg
assets/images/image-gallery/thumb/thumb-11.jpg
assets/images/image-gallery/thumb/thumb-12.jpg
assets/images/image-gallery/thumb/thumb-13.jpg
assets/images/image-gallery/thumb/thumb-14.jpg
assets/images/image-gallery/thumb/thumb-15.jpg
assets/images/weather/cloudy.svg
assets/images/weather/rain.svg
assets/images/weather/sky.svg
assets/images/weather/summer.svg
PATHS

# Download commonly used plugins from demo
while IFS= read -r path; do
  download "$path"
done <<'PLUGINS'
assets/plugins/bootstrap/css/bootstrap.min.css
assets/plugins/bootstrap-select/css/bootstrap-select.css
assets/plugins/morrisjs/morris.min.css
assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css
assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css
PLUGINS

echo "Done. Images in $ASSETS/images"
