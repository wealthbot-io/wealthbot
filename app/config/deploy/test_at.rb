set :domain, 'ec2-52-38-224-236.us-west-2.compute.amazonaws.com'
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