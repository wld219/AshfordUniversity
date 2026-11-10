CREATE TABLE IF NOT EXISTS Advising_Reschedule_Proposal (
 proposal_ID INT AUTO_INCREMENT PRIMARY KEY,
 appointment_ID INT NOT NULL,
 proposed_By_ID INT NOT NULL,
 proposed_DateTime DATETIME NOT NULL,
 reason VARCHAR(500) NOT NULL,
 status ENUM('Pending','Accepted','Declined') NOT NULL DEFAULT 'Pending',
 created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 resolved_At DATETIME NULL,
 INDEX (appointment_ID,status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Advising_Activity (
 activity_ID INT AUTO_INCREMENT PRIMARY KEY,
 appointment_ID INT NOT NULL,
 actor_ID INT NOT NULL,
 recipient_ID INT NOT NULL,
 message VARCHAR(500) NOT NULL,
 created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 seen_At DATETIME NULL,
 INDEX (recipient_ID,created_At),
 INDEX (appointment_ID)
) ENGINE=InnoDB;
