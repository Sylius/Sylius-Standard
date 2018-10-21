### TRAVIS CI
# ¯¯¯¯¯¯¯¯¯¯¯

define chenv
	set -a && source .env.test_cached && set +a; \
	$(1)
endef

ci-symfony-before-install: ##  Symfony test: before install
	phpenv config-rm xdebug.ini || true

	echo "memory_limit=4096M" >> ~/.phpenv/versions/$(shell phpenv version-name)/etc/conf.d/travis.ini
	echo "extension = memcached.so" >> ~/.phpenv/versions/$(shell phpenv version-name)/etc/conf.d/travis.ini

	cp .env.test_cached.dist .env.test_cached

ci-symfony-install: ##  Symfony test: install
	if [ ! -z "${SYMFONY_VERSION}" ]; then bin/require-symfony-version composer.json "${SYMFONY_VERSION}"; fi
	composer update --no-interaction --prefer-dist
	yarn install

ci-symfony-before-script: ##  Symfony test: before script
	bin/console doctrine:database:create --env=test_cached -vvv # Have to be run with debug = true, to omit generating proxies before setting up the database
	bin/console cache:warmup --env=test_cached --no-debug -vvv
	bin/console doctrine:migrations:migrate --no-interaction --env=test_cached --no-debug -vvv

	bin/console assets:install public --env=test_cached --no-debug -vvv
	yarn build

	# Configure display
	/sbin/start-stop-daemon --start --quiet --pidfile /tmp/xvfb_99.pid --make-pidfile --background --exec /usr/bin/Xvfb -- :99 -ac -screen 0 1680x1050x16
	export DISPLAY=:99

	# Download and configure ChromeDriver
	if [ ! -f ${SYLIUS_CACHE_DIR}/chromedriver ] || [ "$(shell ${SYLIUS_CACHE_DIR}/chromedriver --version | grep -c 2.34)" = "0" ]; then \
	    curl http://chromedriver.storage.googleapis.com/2.34/chromedriver_linux64.zip > chromedriver.zip; \
	    unzip chromedriver.zip; \
	    chmod +x chromedriver; \
	    mv chromedriver ${SYLIUS_CACHE_DIR}; \
	fi

	# Run ChromeDriver
	${SYLIUS_CACHE_DIR}/chromedriver > /dev/null 2>&1 &

	# Download and configure Selenium
	if [ ! -f ${SYLIUS_CACHE_DIR}/selenium.jar ] || [ "$(shell java -jar ${SYLIUS_CACHE_DIR}/selenium.jar --version | grep -c 3.4.0)" = "0" ]; then \
	    curl http://selenium-release.storage.googleapis.com/3.4/selenium-server-standalone-3.4.0.jar > selenium.jar; \
	    mv selenium.jar ${SYLIUS_CACHE_DIR}; \
	fi

	# Run Selenium
	java -Dwebdriver.chrome.driver=${SYLIUS_CACHE_DIR}/chromedriver -jar ${SYLIUS_CACHE_DIR}/selenium.jar > /dev/null 2>&1 &

	# Run webserver
	bin/console server:run localhost:8080 -d public --env=test_cached --no-debug --quiet > /dev/null 2>&1 &

ci-symfony-script: ##  Symfony test: script
	composer validate --strict

	vendor/bin/ecs check src

	vendor/bin/phpspec run --no-interaction -f dot

	bin/console sylius:fixtures:load --no-interaction --env=test_cached --no-debug -vvv

	echo "Testing (Behat, without javascript scenarios; ~@javascript && ~@todo && ~@cli)" "Sylius"
	vendor/bin/behat --strict --no-interaction -vvv -f progress -p cached --tags="~@javascript && ~@todo && ~@cli"

	echo "Testing (Behat, only javascript scenarios; @javascript && ~@todo && ~@cli)" "Sylius"
	vendor/bin/behat --strict --no-interaction -vvv -f progress -p cached --tags="@javascript && ~@todo && ~@cli" || vendor/bin/behat --strict --no-interaction -vvv -f progress -p cached --tags="@javascript && ~@todo && ~@cli" --rerun

ci-symfony-after-failure: ##  Symfony test: after failure
	vendor/lakion/mink-debug-extension/travis/tools/upload-textfiles "${SYLIUS_BUILD_DIR}/*.log"
	IMGUR_API_KEY=4907fcd89e761c6b07eeb8292d5a9b2a vendor/lakion/mink-debug-extension/travis/tools/upload-screenshots "${SYLIUS_BUILD_DIR}/*.png"

ci-docker-before-install:  ##  Docker test: before install
	# Install custom version of docker-composer
	sudo rm /usr/local/bin/docker-compose
	curl -L https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-`uname -s`-`uname -m` > docker-compose
	chmod +x docker-compose
	sudo mv docker-compose /usr/local/bin

	# Shutdown vanilla mysql
	sudo service mysql stop
	while sudo lsof -Pi :3306 -sTCP:LISTEN -t; do sleep 1; done

ci-docker-script:  ##  Docker test: script
	docker-compose --version
	docker-compose pull --ignore-pull-failures || true
	$(call chenv,docker-compose build --pull)
	$(call chenv,docker-compose up -d)

	sleep 60

	$(call chenv,docker-compose exec php bin/console sylius:fixtures:load --no-interaction)

	curl http://localhost/

ci-docker-before-deploy: ##  Docker test: before deploy
	echo "${DOCKER_PASSWORD}" | docker login --username "${DOCKER_USERNAME}" --password-stdin "${DOCKER_REGISTRY}"
