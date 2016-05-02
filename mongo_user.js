conn = new Mongo();
db = conn.getDB("wealthbot");
db.createUser(
    {
      user: "webo",
      pwd: "password",
      roles: [
         "readWrite"
      ]
    }
)
