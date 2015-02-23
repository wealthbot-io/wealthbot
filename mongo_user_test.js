conn = new Mongo();
db = conn.getDB("wealthbot_test");
db.addUser(
    {
      user: "root",
      pwd: "password",
      roles: [
         "readWrite"
      ]
    }
)
