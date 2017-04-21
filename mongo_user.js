conn = new Mongo();
db = conn.getDB("wealthbot");
user = 0;
user = db.wealthbot.users.find({ user: "webo" }).count();
if (user == 0) {
	db.createUser(
	    {
	      user: "webo",
	      pwd: "password",
	      roles: [
	         "readWrite"
	      ]
	    }
	)
}