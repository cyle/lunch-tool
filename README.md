# The Lunch Tool

Organizing lunch for a group of people in multiple offices across campus is annoying. We have instant messages, we have email, we have a chatroom, we have phones, but nobody wants the sole responsibility of coordinating where we eat for lunch. Sometimes it's beautiful outside and we want to go out there -- but some people don't.

So I built this tool to do it for us. This system is simply a voting system. We always eat lunch at noon. Voting opens at 9am every day. Everyone gets a reminder via email at 11am to vote, if they haven't already. At 11:45am it sends out another email to everyone with the result of the vote. That's it!

It sounds simple, but it's amazingly effective. Voting is anonymous, so nobody can blame anyone for the end result. The page even tries to scrape weather data from Google to help figure out whether it's even a good idea to eat outside.

## Notes

I coded this for my coworkers at Emerson College, so it has a lot of Emerson-isms about it. For example, the username field in the database is "ecnet", which is synonymous with username.

The email it generates to remind people to vote is from "Cylebot", which is what I put at the end of all of my automated emails. You might want to change that, but whatever.

Also, when it emails people in "reminder.php" and "result.php", it uses the person's username and appends it with "@whatever.com" -- because at Emerson everyone has an @emerson.edu address. You might want to change that by adding a database field for "email" or something.

We only had three options in our case: 

1. Eat outside.
2. Eat inside.
3. I don't care / I won't be there.

Also, most importantly, this system requires a login system, referenced as "login\_check.php", but is not included here because the one used is proprietary to Emerson. Any reference to the $current\_user variable will throw an error if you just roll this out as-is. The $current\_user array simply contains values for "loggedin" (boolean), "username" (string), and "user_id" (int).

## Requirements

- A linux-based web server, Apache or lighttpd or whatever
- Ability to send mail from the server
- PHP 5.3+
- MySQL 5.0+
- The "Mail" PHP PEAR package -- you can use the built-in mail() function but it'll require refactoring

## Installing

Put it anywhere, really. Run the included SQL file to set up the database tables.

There are two cron jobs which send the two emails, so you'll need to set those up on your server. One runs at 11:00am to run "reminder.php" and the other runs at 11:45am to run "result.php".

Finally, you need to go into the database and actually add the options for voting on. I didn't create any admin interface for this.