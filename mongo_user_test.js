conn = new Mongo();
db = conn.getDB("wealthbot_test");
db.createUser(
    {
      user: "webo",
      pwd: "password",
      roles: [
         "readWrite"
      ]
    }
)
