-- Run once: mysql -u root -p ashford_waleed < admin/migrations/section_demo_enrollment.sql
CREATE TABLE IF NOT EXISTS Course_Section_Demo_Enrollment (
  CRN INT NOT NULL PRIMARY KEY,
  demo_enrolled TINYINT UNSIGNED NOT NULL,
  CONSTRAINT fk_section_demo_crn FOREIGN KEY (CRN) REFERENCES Course_Section(CRN) ON DELETE CASCADE,
  CONSTRAINT chk_section_demo_count CHECK (demo_enrolled BETWEEN 0 AND 10)
) ENGINE=InnoDB;
