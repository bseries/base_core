#!/bin/bash
#
# Base Core
#
# Copyright (c) 2016 Atelier Disko - All rights reserved.
#
# Licensed under the AD General Software License v1.
#
# This software is proprietary and confidential. Redistribution
# not permitted. Unless required by applicable law or agreed to
# in writing, software distributed on an "AS IS" BASIS, WITHOUT-
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#
# You should have received a copy of the AD General Software
# License. If not, see https://atelierdisko.de/licenses.
#

set -o nounset
set -o errexit

# Must be executed from the library root. Will operate on and modify the
# *current* files in tree. Be sure to operate on a copy.
[[ ! -d config ]] && echo "error: not invoked from library root" && exit 1

for f in $(ls resources/g11n/po/*/LC_MESSAGES/*.po); do
	msgfmt -o ${f/.po/.mo} --verbose $f
done

for f in $(find assets/js -type f -name *.js); do
	if [[ $(basename $f) == jquery.js ]]; then
		# yui does not work with jquery 2.2
		# https://github.com/yui/yuicompressor/issues/234
		closure-compiler --js $f --js_output_file $f.min && mv $f.min $f
	else
		yuicompressor --type js -o $f.min --nomunge --charset utf-8 $f && mv $f.min $f
	fi
done

for f in $(ls assets/*.css); do
	myth $f $f
	# yuicompressor breaks spaces in calc() expressions
	sqwish $f -o $f.min && mv $f.min $f
done
