SUPPORTED_PLUGINS = marketplace-plugin

.PHONY: install-sylius-plugin
install-sylius-plugin:
	@set +x; \
	\
	UNCOMMITTED_CHANGES=$$(git status --porcelain); \
	if [ -n "$$UNCOMMITTED_CHANGES" ]; then \
	  echo -e "\n\033[1;31mUncommitted changes detected!\033[0m"; \
	  echo "We advise you to commit or stash them before continuing."; \
	  read -p "Do you still want to proceed? (y/n) " ANSWER; \
	  if [ "$$ANSWER" != "y" ]; then \
		echo -e "\033[1;31mAborted by user.\033[0m"; \
		exit 1; \
	  fi; \
	fi; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Checking 'PLUGIN' argument..."; \
	if [ -z "$(PLUGIN)" ]; then \
	  echo -e "\033[1;31mError:\033[0m No plugin specified."; \
	  echo "Please run:"; \
	  echo "  make install-sylius-plugin PLUGIN=<plugin-name>"; \
	  exit 1; \
	fi; \
	if ! echo "$(SUPPORTED_PLUGINS)" | grep -w -q "$(PLUGIN)"; then \
	  echo -e "\033[1;31mError:\033[0m The plugin '$(PLUGIN)' is not supported."; \
	  echo -e "\033[1;33mSupported plugins are:\033[0m"; \
	  for sp in $(SUPPORTED_PLUGINS); do \
		echo -e "  - \033[1;32m$$sp\033[0m"; \
	  done; \
	  exit 1; \
	fi; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Checking Sylius Packagist token..."; \
	SYLIUS_PACKAGIST_TOKEN=$$(composer config --global --auth http-basic.sylius.repo.packagist.com.password 2>/dev/null || echo ""); \
	if [ -z "$$SYLIUS_PACKAGIST_TOKEN" ]; then \
	  echo -e "\033[1;33mNo SYLIUS_PACKAGIST_TOKEN found.\033[0m"; \
	  read -p "Enter your Sylius Packagist token: " SYLIUS_PACKAGIST_TOKEN; \
	  if [ -z "$$SYLIUS_PACKAGIST_TOKEN" ]; then \
	    echo -e "\033[1;31mError:\033[0m No token provided. Aborting."; \
	    exit 1; \
	  fi; \
	  composer config --global http-basic.sylius.repo.packagist.com token "$$SYLIUS_PACKAGIST_TOKEN" || { \
	    echo -e "\033[1;31mError:\033[0m Failed to set the token."; \
	    exit 1; \
	  }; \
	fi; \
	echo -e "\033[1;32mValidating token...\033[0m"; \
	curl -sf -u token:$$SYLIUS_PACKAGIST_TOKEN https://sylius.repo.packagist.com/sylius/packages.json > /dev/null || { \
	  echo -e "\033[1;31mError:\033[0m Invalid token provided. Aborting."; \
	  exit 1; \
	}; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Configuring Sylius repository..."; \
	composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/ || { \
	  echo -e "\033[1;31mError:\033[0m Failed to configure the Sylius repository."; \
	  exit 1; \
	}; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Installing plugin '$(PLUGIN)'..."; \
	composer require $(PLUGIN) --no-scripts --no-interaction || { \
	  echo -e "\033[1;31mError:\033[0m Failed to install plugin '$(PLUGIN)'."; \
	  echo -e "\033[1;33mCheck the token or plugin name.\033[0m"; \
	  exit 1; \
	}; \
	echo -e "\033[1;32mPlugin '$(PLUGIN)' installed successfully.\033[0m"; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Running Rector for code cleanup..."; \
	vendor/bin/rector process src --no-progress-bar --no-diffs || { \
	  echo -e "\033[1;31mError:\033[0m Rector process failed."; \
	  exit 1; \
	}; \
	echo -e "\033[1;32mRector process completed successfully.\033[0m"; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Warming up Symfony cache..."; \
	bin/console cache:warmup || { \
	  echo -e "\033[1;31mError:\033[0m Cache warmup failed."; \
	  exit 1; \
	}; \
	echo -e "\033[1;32mCache warmed up successfully.\033[0m"; \
	\
	echo -e "\033[1;34m[Sylius Plugin Installer]\033[0m Running migrations..."; \
	bin/console doctrine:migrations:migrate --no-interaction || { \
	  echo -e "\033[1;31mError:\033[0m Migrations failed."; \
	  exit 1; \
	}; \
	echo -e "\033[1;32mMigrations completed successfully.\033[0m"; \
	\
	echo -e "\n\033[1;34m[Sylius Plugin Installer]\033[0m Copying required Sylius templates..."; \
	TEMPLATES="\
		bundles/SyliusAdminBundle/Order/Show/Summary/_totals.html.twig \
		bundles/SyliusAdminBundle/Product/Show/_header.html.twig \
		bundles/SyliusCoreBundle/Email/Blocks/OrderConfirmation/_content.html.twig \
		bundles/SyliusUiBundle/Modal/_confirmation.html.twig \
		bundles/SyliusUiBundle/_flashes.html.twig \
		bundles/SyliusShopBundle/Taxon/_horizontalMenu.html.twig \
		bundles/SyliusShopBundle/Register/_header.html.twig \
		bundles/SyliusShopBundle/ProductReview/create.html.twig \
		bundles/SyliusShopBundle/Product/_box.html.twig \
		bundles/SyliusShopBundle/Product/Show/_reviews.html.twig \
		bundles/SyliusShopBundle/Order/_summary.html.twig \
		bundles/SyliusShopBundle/Common/Form/_login.html.twig \
		bundles/SyliusShopBundle/Checkout/_header.html.twig \
		bundles/SyliusShopBundle/Account/Order/Show/_header.html.twig \
	"; \
	for file in $$TEMPLATES; do \
	  mkdir -p templates/$$(dirname $$file); \
	  cp vendor/sylius/plus-marketplace-suite-plugin/templates/$$file templates/$$file; \
	done; \
	echo -e "\033[1;32mRequired Sylius templates copied successfully.\033[0m"; \
	\
	echo -e "\n\033[1;34m[Sylius Plugin Installer]\033[0m Asking user about optional templates..."; \
	read -p "Do you want to copy optional marketplace templates (replace Sylius branding/logos, etc.)? [y/n]: " CONFIRM_COPY; \
	if [ "$$CONFIRM_COPY" = "y" ]; then \
	  OPTIONAL_TEMPLATES="\
	    bundles/SyliusAdminBundle/Layout/_logo.html.twig \
	    bundles/SyliusAdminBundle/Layout/_notification.html.twig \
	    bundles/SyliusAdminBundle/Security/login.html.twig \
	    bundles/SyliusAdminBundle/layout.html.twig \
	    bundles/SyliusCoreBundle/Email/layout.html.twig \
	    bundles/SyliusUiBundle/Layout/centered.html.twig \
	    bundles/SyliusUiBundle/Security/_logo.html.twig \
	    bundles/TwigBundle/Exception \
	    bundles/SyliusShopBundle/Layout/Header/_logo.html.twig \
	    bundles/SyliusShopBundle/Homepage/_banner.html.twig \
	  "; \
	  for file in $$OPTIONAL_TEMPLATES; do \
	    src="vendor/sylius/plus-marketplace-suite-plugin/templates/$$file"; \
	    dest="templates/$$file"; \
	    if [ -d "$$src" ]; then \
	      mkdir -p "$$dest"; \
	      cp -r "$$src/"* "$$dest"/; \
	    else \
	      mkdir -p "$$(dirname $$dest)"; \
	      cp "$$src" "$$dest"; \
	    fi; \
	  done; \
	  echo -e "\033[1;32mOptional marketplace templates copied successfully.\033[0m"; \
	else \
	  echo -e "\033[1;33mSkipping optional marketplace templates.\033[0m"; \
	fi; \
	\
	echo -e "\n\033[1;34m[Sylius Plugin Installer]\033[0m Final cache warmup..."; \
	bin/console cache:warmup; \
	echo -e "\033[1;32mDone warming up cache.\033[0m"; \
	\
	echo -e "\n\033[1;34m[Sylius Plugin Installer]\033[0m Installing assets..."; \
	bin/console assets:install; \
	echo -e "\nWould you like me to run yarn encore now?"; \
	read -p "Type 'dev', 'production' or 'skip' [dev/production/skip]: " BUILD_CHOICE; \
	case "$$BUILD_CHOICE" in \
	  dev) \
	    echo -e "\033[1;34mRunning 'yarn encore dev'...\033[0m"; \
	    yarn encore dev; \
	    ;; \
	  production) \
	    echo -e "\033[1;34mRunning 'yarn encore production'...\033[0m"; \
	    yarn encore production; \
	    ;; \
	  *) \
	    echo -e "\033[1;33mSkipping front-end build.\033[0m You can do it manually later."; \
	    ;; \
	esac; \
	\
	echo -e "\n\033[1;32mAll done!\033[0m Plugin '$(PLUGIN)' installed successfully."
