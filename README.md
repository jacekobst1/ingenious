## Invoice management system

#### Setup Instructions:

* Start the project by running `./start.sh`.
* To access the container environment, use: `docker compose exec app bash`.

#### Task requirements:

- [requirements.md](requirements.md)

#### Architecture Decision Log:

- [Exception driven flow](adl/exception-driven-flow.md)
- [Using concrete application services without interfaces](adl/concrete-application-services.md)
- [Using Eloquent ORM instead of Raw Database Queries](adl/eloquent-instead-of-raw-queries.md)
- [Using Native PHP Arrays instead of Laravel Collections](adl/arrays-instead-of-collections.md)
- [Dynamically calculating invoice total price](adl/calculated-vs-persisted-total-price.md)

#### Missing features that should be implemented:

- API versioning.
- API endpoints documentation (with OpenAPI or similar tool).
- Mechanism for building JSON responses to maintain a consistent structure across the whole codebase.

#### Code quality tools used:

- _larastan/larastan_ - enables static analysis of the code.
- _laravel/pint_ - enforces common code style.
- _deptrac/deptrac_ - guards the boundaries between modules.
- _captainhook/captainhook_ - run code quality checks before every commit and push.
- _ramsey/conventional-commits_ - enforces commit message convention (
  see [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)).
