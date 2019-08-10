# SoftwareDevelopment
CSC350 Course taken at BMCC.  


Scheduling Program using MYSQL and PHP

Group Members:
Daniel Racz - Project Manager
MingMing Jiang - PHP Developer
Anastasiia Burlya - Graphic Designer
Nay Ayeyar - Web Designer



The program that our group created allows a user to schedule courses for the CIS department associated with BMCC.
The goal of the program is to efficiently schedule a given number of courses to available rooms without any conflict of time and days. 
The program consists of an HTML web page for the user to interact with and submit their courses.
The HTML is then associated with a PHP code that contains functions and special cases to be able to schedule the course depending on the room given. 
The PHP connects to the local database where two separate databases are used for storage – courses and rooms. The course database holds information such as the course title and determined section number.
The rooms database then offers a list of individual tables with the room names.
These tables are created when the user schedules a course for the first time. With this method the program can affectively store and print out the results for the user.
The output of the program prints out all the scheduled courses for all the rooms selected in the database. It is ordered by the course section number, which is in relation to the initial time the course starts. If there is another course with a similar section scheduled later, the section will increment by one so that there are no same section numbers for a given course. Followed by the section number are the days the course meets, the start and end time and the room number. The second output shows a weekly grid visual of the classes booked in a specific room. All seven days of the week are displayed with the courses below they are scheduled for.
