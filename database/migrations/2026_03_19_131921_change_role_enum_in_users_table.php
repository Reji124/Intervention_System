<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
public function up()
{
    // Create the enum type if it doesn't exist
    DB::statement("DO $$ BEGIN
        IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role') THEN
            CREATE TYPE user_role AS ENUM ('admin', 'assistant');
        END IF;
    END $$");

    // Change the column type
    DB::statement("ALTER TABLE users ALTER COLUMN role TYPE user_role USING role::user_role");
    DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'assistant'");
    DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
}

public function down()
{
    DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255) USING role::VARCHAR");
    DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'assistant'");
}
};