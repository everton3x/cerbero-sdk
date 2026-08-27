BEGIN TRANSACTION;
DROP TABLE IF EXISTS "crb_permissions";
CREATE TABLE "crb_permissions" (
	"system_slug"	TEXT NOT NULL,
	"slug"	TEXT NOT NULL,
	"description"	TEXT,
	"status"	INTEGER,
	PRIMARY KEY("system_slug","slug")
);
DROP TABLE IF EXISTS "crb_profile_permission";
CREATE TABLE "crb_profile_permission" (
	"system_slug"	TEXT NOT NULL,
	"profile_slug"	TEXT NOT NULL,
	"permission_slug"	TEXT NOT NULL,
	"status"	INTEGER,
	UNIQUE("system_slug","profile_slug","permission_slug")
);
DROP TABLE IF EXISTS "crb_profiles";
CREATE TABLE "crb_profiles" (
	"system_slug"	TEXT NOT NULL,
	"slug"	TEXT NOT NULL,
	"name"	TEXT,
	"status"	INTEGER,
	PRIMARY KEY("system_slug","slug")
);
DROP TABLE IF EXISTS "crb_systems";
CREATE TABLE "crb_systems" (
	"slug"	TEXT NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"status"	INTEGER,
	PRIMARY KEY("slug")
);
DROP TABLE IF EXISTS "crb_user_permission";
CREATE TABLE "crb_user_permission" (
	"user_id"	TEXT NOT NULL,
	"system_slug"	TEXT NOT NULL,
	"permission_slug"	TEXT NOT NULL,
	"status"	INTEGER,
	UNIQUE("user_id","system_slug","permission_slug")
);
DROP TABLE IF EXISTS "crb_user_profile";
CREATE TABLE "crb_user_profile" (
	"system_slug"	TEXT NOT NULL,
	"profile_slug"	TEXT NOT NULL,
	"user_id"	TEXT NOT NULL,
	"status"	INTEGER,
	UNIQUE("system_slug","profile_slug","user_id")
);
DROP TABLE IF EXISTS "crb_user_system";
CREATE TABLE "crb_user_system" (
	"user_id"	TEXT NOT NULL,
	"system_slug"	TEXT NOT NULL,
	"status"	INTEGER,
	UNIQUE("user_id","system_slug")
);
DROP TABLE IF EXISTS "crb_users";
CREATE TABLE "crb_users" (
	"id"	TEXT NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"status"	INTEGER NOT NULL,
	"password_hash"	TEXT,
	"session_token"	TEXT,
	"login_attempts"	INTEGER DEFAULT 0,
	PRIMARY KEY("id")
);
COMMIT;
