conn = new Mongo();
db = conn.getDB("wealthbot_test");
db.addUser(
    {
      user: "webo",
      pwd: "password",
      roles: [
         "readWrite"
      ]
    }
)
