<?php
namespace App\Modules\Customer\Domain\Contracts;
use App\Models\CustomerAddress;
interface AddressRepositoryInterface{public function listForUser(int $userId):iterable;public function defaultForUser(int $userId):?CustomerAddress;public function findForUser(int $userId,int $addressId):CustomerAddress;public function createForUser(int $userId,array $data):CustomerAddress;public function update(CustomerAddress $address,array $data):CustomerAddress;public function delete(CustomerAddress $address):void;}
