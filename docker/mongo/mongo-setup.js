db.createUser(
    {
        user: "webo",
        pwd: "password",
        roles: [ { role: "dbOwner", db: "wealthbot" } ]
    }
);
