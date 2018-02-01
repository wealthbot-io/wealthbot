MAKE_MD = examples.md

all: $(MAKE_MD) $(patsubst %.md, %.html, $(wildcard *.md ../*.md) $(MAKE_MD)) CHANGELOG.html

examples.md: examples_header.md examples/*.md
	cat examples_header.md > examples.md
	for f in examples/*.md; do \
		f=`basename $$f .md` ; \
		echo "* [$$f provider](#$$f-provider)" ; \
	done >> examples.md
	echo >> examples.md
	for f in examples/*.md; do \
		(echo; cat $$f) >> examples.md; \
	done

CHANGELOG.html: ../CHANGELOG.md
	pandoc -o `basename $@` -f markdown -t html -c buttondown.css -S $<

%.html: %.md
	pandoc -o `basename $@` -f markdown -t html -c buttondown.css -S $<

.PHONY: clean
clean:
	rm -f *.html $(MAKE_MD)
