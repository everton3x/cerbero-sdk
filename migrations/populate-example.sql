BEGIN TRANSACTION;
INSERT INTO "crb_permissions" ("system_slug","slug","description","status") VALUES ('example','create','Criação',1),
 ('example','read','Leitura',1),
 ('example','update','Alteração',1),
 ('example','delete','Exclusão',1);
INSERT INTO "crb_profile_permission" ("system_slug","profile_slug","permission_slug","status") VALUES ('example','edit','create',1),
 ('example','edit','read',1),
 ('example','edit','update',1),
 ('example','edit','delete',3);
INSERT INTO "crb_profiles" ("system_slug","slug","name","status") VALUES ('example','edit','Edição',1);
INSERT INTO "crb_systems" ("slug","name","status") VALUES ('example','Sistema de exemplo',1),
 ('test','Sistema de teste',3);
INSERT INTO "crb_user_permission" ("user_id","system_slug","permission_slug","status") VALUES ('admin','example','create',1),
 ('admin','example','read',1),
 ('admin','example','update',1),
 ('admin','example','delete',1),
 ('guest','example','read',1);
INSERT INTO "crb_user_profile" ("system_slug","profile_slug","user_id","status") VALUES ('example','edit','editor',1),
 ('','','',NULL);
INSERT INTO "crb_user_system" ("user_id","system_slug","status") VALUES ('admin','example',1),
 ('editor','example',1),
 ('guest','example',1),
 ('admin','test',1);
INSERT INTO "crb_users" ("id","name","status","password_hash","session_token") VALUES ('admin','Administrador',1,'$argon2id$v=19$m=65536,t=4,p=1$Ni56SjI4OHpUZFZDT3V0bA$tS0ui5LLpuEOPR7v8X8+UUv3DRXFMo2U/8nH6A6+ewg',NULL),
 ('editor','Editor',1,'$argon2id$v=19$m=65536,t=4,p=1$Ni56SjI4OHpUZFZDT3V0bA$tS0ui5LLpuEOPR7v8X8+UUv3DRXFMo2U/8nH6A6+ewg',NULL),
 ('guest','Visitante',1,'$argon2id$v=19$m=65536,t=4,p=1$Ni56SjI4OHpUZFZDT3V0bA$tS0ui5LLpuEOPR7v8X8+UUv3DRXFMo2U/8nH6A6+ewg',NULL),
 ('fake','Usuário fake',3,'$argon2id$v=19$m=65536,t=4,p=1$Ni56SjI4OHpUZFZDT3V0bA$tS0ui5LLpuEOPR7v8X8+UUv3DRXFMo2U/8nH6A6+ewg',NULL);
COMMIT;
