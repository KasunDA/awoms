<?php

class HomeController extends Controller
{

  // Home
  public function home() {
    $this->set('title', BRAND);
  }

}