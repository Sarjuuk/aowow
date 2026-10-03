"""Require one completed PASS marker from Chrome's executed fixture DOM."""
from html.parser import HTMLParser
from pathlib import Path
import re
import sys


class Results(HTMLParser):
    def __init__(self):
        super().__init__()
        self.values = []
        self.capture = False

    def handle_starttag(self, tag, attrs):
        if tag == 'pre' and dict(attrs).get('id') == 'result':
            self.values.append('')
            self.capture = True

    def handle_data(self, data):
        if self.capture:
            self.values[-1] += data

    def handle_endtag(self, tag):
        if tag == 'pre':
            self.capture = False


parser = Results()
parser.feed(Path(sys.argv[1]).read_text())
if len(parser.values) != 1 or not re.fullmatch(r'PASS: [1-9][0-9]* .*checks\.?', parser.values[0]):
    raise SystemExit(f'Browser fixture failed or did not finish: {parser.values!r}')
print(parser.values[0])
