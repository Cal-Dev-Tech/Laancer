SHELL := /opt/homebrew/bin/zsh
ROOT := /Volumes/SSK_SSD/Work/Laancer/public_html

start:
	bash $(ROOT)/scripts/start.sh

stop:
	bash $(ROOT)/scripts/stop.sh

status:
	bash $(ROOT)/scripts/status.sh
