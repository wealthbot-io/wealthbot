set :domain, 'demo.wealthbot.io'
role :app, domain, :primary => true
role :web, domain, :primary => true
role :db,  domain, :primary => true

set :deploy_to,   "/var/www"
set :user, "ubuntu"
set :clear_controllers,     false

##############

set :branch, "master"

set :repository,  "git@github.com:azatyan/wealthbot.git"

set :deploy_via, :copy