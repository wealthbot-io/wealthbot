set :domain, 'ec2-52-39-164-144.us-west-2.compute.amazonaws.com'
role :app, domain, :primary => true
role :web, domain, :primary => true
role :db,  domain, :primary => true

set :deploy_to,   "/var/www/release/wea.app"
set :user, "ubuntu"
set :clear_controllers,     false

##############

set :branch, "master"

set :repository,  "git@github.com:azatyan/wealthbot.git"

set :deploy_via, :copy
