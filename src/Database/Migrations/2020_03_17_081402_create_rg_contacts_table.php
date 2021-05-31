<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_contacts', function (Blueprint $table) {
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
            $table->string('external_key', 250);
            $table->unsignedInteger('app_id')->nullable();
            $table->boolean('system_created')->nullable();

            $table->unsignedBigInteger('banking_bank_id')->nullable();
            $table->json('types');
            $table->string('status', 10);
            $table->string('image', 250)->nullable();
            $table->string('salutation', 10)->nullable();
            $table->string('name', 100);
            $table->string('display_name', 100);
            $table->string('contact_salutation', 10)->nullable();
            $table->string('contact_first_name', 50)->nullable();
            $table->string('contact_last_name', 50)->nullable();
            $table->string('contact_email', 250)->nullable();
            $table->string('contact_work_phone', 15)->nullable();
            $table->string('contact_mobile', 15)->nullable();
            $table->string('currency', 3);
            $table->json('currencies');
            $table->string('country', 2);
            $table->string('payment_terms', 50)->nullable();
            $table->string('facebook_link', 250)->nullable();
            $table->string('twitter_link', 250)->nullable();
            $table->string('billing_address_attention')->nullable();
            $table->string('billing_address_street1')->nullable();
            $table->string('billing_address_street2')->nullable();
            $table->string('billing_address_city')->nullable();
            $table->string('billing_address_state')->nullable();
            $table->string('billing_address_zip_code')->nullable();
            $table->string('billing_address_country')->nullable();
            $table->string('billing_address_fax')->nullable();
            $table->string('shipping_address_attention')->nullable();
            $table->string('shipping_address_street1')->nullable();
            $table->string('shipping_address_street2')->nullable();
            $table->string('shipping_address_city')->nullable();
            $table->string('shipping_address_state')->nullable();
            $table->string('shipping_address_zip_code')->nullable();
            $table->string('shipping_address_country')->nullable();
            $table->string('shipping_address_fax')->nullable();
            $table->string('remarks')->nullable();
            $table->json('taxes')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_contacts');
    }
}
