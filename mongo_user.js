conn = new Mongo();
db = conn.getDB("wealthbot");
db.addUser(
    {
      user: "webo",
      pwd: "password",
      roles: [
         "readWrite"
      ]
    }
)
