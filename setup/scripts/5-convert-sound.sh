#!/bin/bash

set -u

if ! FFMPEG="$(command -v ffmpeg)"; then
    printf 'Error: ffmpeg not installed.\n' >&2
    exit 1
fi

convert_sound_directory() {
    local source_dir="$1"
    local output_dir="${source_dir%/sound}/_sound"
    local input_file relative_file output_file

    [[ -d "$source_dir" ]] || return 0

    mkdir -p $output_dir

    while IFS= read -r -d '' input_file; do
        relative_file="${input_file#"$source_dir"/}"
        output_file="$output_dir/$relative_file"

        printf 'Converting: %s\n' "$input_file"
        "$FFMPEG" -hide_banner -loglevel error -y -i "$input_file" "$output_file"
    done < <(find "$source_dir" -type f -print0)
}

convert_sound_directory "../mpqdata/sound"

while IFS= read -r -d '' locale_dir; do
    convert_sound_directory "$locale_dir/sound"
done < <(find "../mpqdata" -mindepth 1 -maxdepth 1 -type d -regextype posix-extended -regex '.*/[[:alpha:]]{4}' -print0)
