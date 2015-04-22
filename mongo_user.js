conn = new Mongo();
db = conn.getDB("wealthbot");
db.addUser(
    {
      user: "root",
      pwd: "password",
      roles: [
         "readWrite"
      ]
    }
)
