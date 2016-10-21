<?php

class Table {
  protected $pdo = '';
  public $table = '';
  public $result = '';

  public function __construct($host, $db, $charset, $user, $pass) {
    if ($host && $db && $charset && $user && $pass) die("Set all data please");
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $opt = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );

    $this->pdo= new PDO($dsn, $user, $pass, $opt);
  }

  public function main () {
//реализовать автозаполение в случае если хотим тестовый вариант
  }

  public function useTable($table) {
    $this->table = $table;
  }

  public function createTable ($table) {

    $sql = $this->pdo->prepare('CREATE TABLE ? IF NOT EXISTS');
    $sql->execute($table);

    $this->table = $table;
  }

  public function update ($table, $values, $current_element, $value) {
    $this->transactionBegin();

    $query = $this->pdo->prepare('UPDATE :table SET :values WHERE :current_element = :value');

    if ($query->execute()) {
      $this->transactionSucsessEnd();
    }
    else {
      $this->transactionWasNotSucsess();
    }
  }

  public function delete($table, $values, $current, $val) {
//'DELETE :values FROM :table WHERE :current = :val'
  }

  public function select($table) {

  }

  public function insert($table, $array) {
    $this->transactionBegin();

    $query = $this->pdo->prepare('INSERT INTO :table VALUES :array');

    //продумать множественный INSERT
    if ($query->execute()) {
      $this->transactionSucsessEnd();
    }
    else {
      $this->transactionWasNotSucsess();
    }
  }

  public function transactionBegin() {
    $this->pdo->query('BEGIN;');
    echo "Start transaction";
  }

  public function transactionSucsessEnd() {
    $this->pdo->query('COMMIT;');
    echo "End transaction. Sucsess.";
  }

  public function transactionWasNotSucsess() {
    $this->pdo->query('ROLLBACK;');
    echo "End transaction. Sucsess.";
  }
}
