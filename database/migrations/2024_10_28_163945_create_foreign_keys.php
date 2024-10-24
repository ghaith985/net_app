<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeys extends Migration {

	public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('groups', function (Blueprint $table) {
            $table
                ->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('group_members', function (Blueprint $table) {
            $table
                ->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });


        Schema::table('group_members', function (Blueprint $table) {
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('files', function (Blueprint $table) {
            $table
                ->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('files', function (Blueprint $table) {
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('file_events', function (Blueprint $table) {
            $table
                ->foreign('file_id')
                ->references('id')
                ->on('files')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('file_events', function (Blueprint $table) {
            $table
                ->foreign('event_type_id')
                ->references('id')
                ->on('event_types')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
        Schema::table('file_events', function (Blueprint $table) {
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

    }
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });
        Schema::table('group_members', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::table('file_events', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
            $table->dropForeign(['event_type_id']);
            $table->dropForeign(['user_id']);
        });


    }
}