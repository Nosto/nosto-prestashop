#!/usr/bin/env bash
#find . -type d -exec cp index.php {}/index.php \;
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SOURCE_DIR="$SCRIPT_DIR/../src/nostotagging"
INDEX_DIR="$SCRIPT_DIR/../static"
find $SOURCE_DIR -type d -exec cp $INDEX_DIR/index.php {}/index.php \;