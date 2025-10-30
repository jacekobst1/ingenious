sail := ./vendor/bin/sail

help:
	@echo "Available commands:"; \
	grep -E '^[a-zA-Z_-]+:.*?$$' $(MAKEFILE_LIST) | sed 's/:.*//' | sort

test:
	${sail} artisan test

pint-check:
	${sail} run ./vendor/bin/pint --test $(filter-out $@,$(MAKECMDGOALS))

pint-fix:
	${sail} run ./vendor/bin/pint

phpstan-check:
	${sail} run ./vendor/bin/phpstan analyse $(filter-out $@,$(MAKECMDGOALS))

phpstan-baseline:
	${sail} run ./vendor/bin/phpstan analyse --generate-baseline

ih:
	${sail} artisan ide-helper:generate
	${sail} artisan ide-helper:meta
	${sail} artisan ide-helper:models -M

# Handle additional arguments passed to make
%:
	@:
