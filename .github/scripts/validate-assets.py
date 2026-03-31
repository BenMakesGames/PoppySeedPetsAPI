import sys
import os
import xml.etree.ElementTree as ET
from io import BytesIO

import cairosvg
from PIL import Image

EXPECTED_DIMENSIONS = {
    "items": (32, 32),
    "pets": (48, 48),
}

has_errors = False


def error(path, message):
    global has_errors
    has_errors = True
    print(f"::error file={path}::{message}")


def warn(path, message):
    print(f"::warning file={path}::{message}")


def get_asset_type(path):
    """Return 'items' or 'pets' based on the file path, or None if neither."""
    parts = path.replace("\\", "/").split("/")
    for i, part in enumerate(parts):
        if part == "images" and i + 1 < len(parts):
            next_part = parts[i + 1]
            if next_part in EXPECTED_DIMENSIONS:
                return next_part
    return None


def check_format(path):
    """Check A: file must be .svg."""
    if not path.lower().endswith(".svg"):
        error(path, f"Non-SVG file found: {os.path.basename(path)}. All items and pets must be SVG files.")
        return False
    return True


def check_dimensions(path, asset_type):
    """Check B: root <svg> must have correct width/height."""
    try:
        tree = ET.parse(path)
    except ET.ParseError as e:
        error(path, f"Failed to parse SVG: {e}")
        return False

    root = tree.getroot()
    # Handle namespaced SVG tag
    tag = root.tag
    if not tag.endswith("svg"):
        error(path, "Root element is not <svg>.")
        return False

    expected_w, expected_h = EXPECTED_DIMENSIONS[asset_type]

    raw_w = root.get("width", "")
    raw_h = root.get("height", "")

    # Strip 'px' suffix
    w = raw_w.replace("px", "").strip()
    h = raw_h.replace("px", "").strip()

    try:
        w_val = int(float(w))
        h_val = int(float(h))
    except ValueError:
        error(path, f"Could not parse dimensions: width=\"{raw_w}\" height=\"{raw_h}\". Expected {expected_w}x{expected_h}.")
        return False

    if w_val != expected_w or h_val != expected_h:
        error(path, f"Wrong dimensions: {w_val}x{h_val}. Expected {expected_w}x{expected_h}.")
        return False

    return True


def check_centering(path):
    """Check C: visual bounding box should be roughly centered (items only)."""
    try:
        png_data = cairosvg.svg2png(url=path, output_width=32, output_height=32)
    except Exception as e:
        warn(path, f"Could not render SVG for centering check: {e}")
        return

    img = Image.open(BytesIO(png_data))
    bbox = img.getbbox()

    if bbox is None:
        warn(path, "SVG appears to be completely transparent (no visible content).")
        return

    left_margin = max(2, bbox[0])
    top_margin = max(2, bbox[1])
    right_margin = max(2, img.width - bbox[2])
    bottom_margin = max(2, img.height - bbox[3])

    issues = []

    if left_margin > 2 * right_margin or right_margin > 2 * left_margin:
        issues.append(f"horizontal: left={bbox[0]}px, right={img.width - bbox[2]}px")

    if top_margin > 2 * bottom_margin or bottom_margin > 2 * top_margin:
        issues.append(f"vertical: top={bbox[1]}px, bottom={img.height - bbox[3]}px")

    if issues:
        detail = "; ".join(issues)
        warn(path, f"Content may not be centered ({detail}). The margin on one side is more than double the other.")


def main():
    files = [line.strip() for line in sys.stdin if line.strip()]

    if not files:
        print("No files to validate.")
        sys.exit(0)

    for path in files:
        asset_type = get_asset_type(path)
        if asset_type is None:
            continue

        if not check_format(path):
            continue

        if not check_dimensions(path, asset_type):
            continue

        if asset_type == "items":
            check_centering(path)

    if has_errors:
        sys.exit(1)


if __name__ == "__main__":
    main()
