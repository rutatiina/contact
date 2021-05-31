<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgContactBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_contact_bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            //>> default columns
            $table->softDeletes();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            //<< default columns

            //>> table columns
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedInteger('contact_id');
            $table->string('country');
            $table->string('bank', 250);
            $table->string('name');
            $table->string('number', 50);
            $table->string('code', 50);
            $table->string('currency', 4);
            $table->string('routing_number', 50);
            $table->string('swift_code', 50);
            $table->string('description', 100);
            $table->unsignedTinyInteger('primary');
            $table->unsignedTinyInteger('active');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_contact_bank_accounts');
    }
}
