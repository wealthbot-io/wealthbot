set :domain, 'dev-upgrade.wealthbot.io'
role :app, domain, :primary => true
role :web, domain, :primary => true
role :db,  domain, :primary => true

set :deploy_to,   "/var/www/release/#{domain}"
set :user, "ubuntu"
set :clear_controllers,     false

##############

set :branch, "feature/upgrade-2.8"