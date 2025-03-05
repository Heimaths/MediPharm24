<?php
include("./models/person.php");
class DataHandler
{
    public function queryPersons()
    {
        $res =  $this->getDemoData();
        return $res;
    }

    public function queryPersonById($id)
    {
        $result = array();
        foreach ($this->queryPersons() as $val) {
            if ($val[0]->id == $id) {
                array_push($result, $val);
            }
        }
        return $result;
    }

    public function queryPersonByName($name)
    {
        $result = array();
        foreach ($this->queryPersons() as $val) {
            if ($val[0]->lastname == $name) {
                array_push($result, $val);
            }
        }
        return $result;
    }

    private static function getDemoData()
    {
        $demodata = [
            [new Person(1, "Jane", "Doe", "jane.doe@fhtw.at", 1234567, "Central IT")],
            [new Person(2, "John", "Doe", "john.doe@fhtw.at", 34345654, "Help Desk")],
            [new Person(3, "Baby", "Doe", "baby.doe@fhtw.at", 54545455, "Management")],
            [new Person(4, "Mike", "Smith", "mike.smith@fhtw.at", 343477778, "Faculty")],
            [new Person(5, "Sarah", "Johnson", "sarah.j@fhtw.at", 98765432, "Research")],
            [new Person(6, "Thomas", "Anderson", "t.anderson@fhtw.at", 45678901, "Development")],
            [new Person(7, "Maria", "Garcia", "m.garcia@fhtw.at", 23456789, "Marketing")]
        ];
        return $demodata;
    }

    public function queryPersonByDepartment($department)
    {
        $result = array();
        foreach ($this->queryPersons() as $val) {
            if ($val[0]->department == $department) {
                array_push($result, $val);
            }
        }
        return $result;
    }

    public function queryPersonByEmail($email)
    {
        $result = array();
        foreach ($this->queryPersons() as $val) {
            if ($val[0]->email == $email) {
                array_push($result, $val);
            }
        }
        return $result;
    }
}
