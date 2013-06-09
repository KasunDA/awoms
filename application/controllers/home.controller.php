<?php

class HomeController extends Controller
{

  // Home
  public function home() {
    $this->set('title', BRAND);
  }
  
  // Test
  public function test() {
    $this->title = 'Test Title!';
    $this->test = 'Test!';
  }
  
  
  // examples below
  
  
  function view($id = null, $name = null) {

    $this->set('title', $name . ' - My Todo List App');
    $this->set('todo', $this->Item->select($id));
  }

  function viewall() {

    $this->set('title', 'All Items - My Todo List App');
    $this->set('todo', $this->Item->selectAll());
  }

  function add() {
    $todo = $_POST['todo'];
    $this->set('title', 'Success - My Todo List App');
    $this->set('todo', $this->Item->query('insert into items (item_name) values (\'' . mysql_real_escape_string($todo) . '\')'));
  }

  function delete($id = null) {
    $this->set('title', 'Success - My Todo List App');
    $this->set('todo', $this->Item->query('delete from items where id = \'' . mysql_real_escape_string($id) . '\''));
  }

}