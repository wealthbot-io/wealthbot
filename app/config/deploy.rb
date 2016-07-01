set :stages, %w(jenkins prod staging test_at)
set :stage_dir,     "./app/config/deploy"
require 'capistrano/ext/multistage'


#################################################################

set :application, "Wealthbot"
set :app_path,    "app"
set :web_path,    "web"

set :repository,  "git@github.com:wealthbot-io/core.git"
set :scm,         :git
set :scm_verbose, false

#ssh settings
set :use_sudo,      false
ssh_options[:paranoid] = false
ssh_options[:forward_agent] = true
default_run_options[:pty] = true


set :model_manager, "doctrine"
set  :keep_releases,  2
set :deploy_via, :remote_cache
set :use_composer, true
#set :update_vendors, true

set :composer_bin, "/usr/local/bin/composer"

set :copy_vendors, true

set :dump_assetic_assets, true
set :interactive_mode, false
set :symfony_env_prod, "prod"

set :shared_files,      ["app/config/parameters.yml", "app/config/parameters_test.yml", "system/Config.php"]
set :shared_children,     [app_path + "/logs", "uploads", "system/incoming_files", "system/outgoing_files"]

# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL

# Dump js routes
before "symfony:assets:install", "symfony:dump_js_routing"

# Uncomment this when we will have migrations.
#before "symfony:cache:warmup", "symfony:doctrine:migrations:migrate"

#TEMPORARY!!! Probably this is not required in future.
# temporary bug fix for v2.15.5
before "symfony:cache:warmup", "symfony:assets:install"
before "symfony:cache:warmup", "symfony:doctrine:schema:update"

# update the assets version to cache-bust on new release
after "symfony:composer:install", "symfony:assets:update_version"

# write a meaningful version file to the webroot
after "deploy:finalize_update", "deploy:write_version_file"

after "deploy", "apc:clear"

#################################################################

namespace :apc do
  task :clear do
    capifony_pretty_print "--> Clear APC cache"
      run "#{current_path}/app/console apc:clear --env=#{symfony_env_prod}"
    capifony_puts_ok
  end
end

#################################################################

after "deploy:finalize_update" do
  run "sudo chown -R www-data:www-data #{latest_release}/#{cache_path}"
  run "sudo chown -R www-data:www-data #{shared_path}/#{log_path}"
  run "sudo chmod 777 #{shared_path}/#{log_path}"
  run "sudo chmod -R 777 #{latest_release}/#{cache_path}"
end

#################################################################

namespace :symfony do
  task :dump_js_routing do
    capifony_pretty_print "--> Dump JS routing"
      run "#{latest_release}/app/console fos:js-routing:dump --env=#{symfony_env_prod}"
    capifony_puts_ok
  end
end


namespace :deploy do
  desc "Write the name of the tag that we're deploying to a VERSION file"
  task :write_version_file do
    capifony_pretty_print "--> Creating the VERSION file for release: #{branch}"
        run "echo -n \"Branch/Tag: #{branch} | Commit #{real_revision}\" > #{release_path}/web/VERSION.html"
    capifony_puts_ok
  end
end

namespace :nginx do
desc "Reload nginx configuration"
    task :reload, :role => :web do
      capifony_pretty_print "--> Reloading Nginx"
      run "#{sudo} /etc/init.d/nginx reload"
    end
end


logger.level = Logger::MAX_LEVEL