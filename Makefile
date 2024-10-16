compose=docker compose

dc:
	@${compose} -f docker-compose.yml $(cmd)

dcr:
	@make dc cmd="run --rm php-cli $(cmd)"

stop:
	@make dc cmd="stop"

up:
	@make dc cmd="up -d"

build-containers:
	@make dc cmd="up -d --build"

down:
	@make dc cmd="down"

console:
	@make dcr cmd="bin/console $(arg)"

csfix:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
migrate-generate:
	vendor/bin/doctrine-migrations generate
migrate-run:
	vendor/bin/doctrine-migrations migrate