<?php
/*
  Plugin Name: Faciliteter
  @package Faciliteter
*/

/**
 * "enqueue_block_editor_assets" körs när
 * det är dags att ladda in alla nya block.
 */
add_action('enqueue_block_editor_assets', 'faciliteter_my_block_files');

function faciliteter_my_block_files()
{
  /**
   * Lägg till vår nya block som finns
   * under "faciliteter-block.js"
   */
  wp_enqueue_script(
    'faciliteter-handle',
    plugin_dir_url(__FILE__) . 'faciliteter-block.js',
    array('wp-blocks', 'wp-i18n', 'wp-editor'),
    null
  );
}

/**
 * "init" körs när plugin-et är igång för första gången.
 */
add_action('init', 'faciliteter_register_block');


function faciliteter_register_block()
{
  /**
   * Lägg till server-delen av vår block.
   * Se till att det första värdet stämmer överens
   * med det namn man angav under "faciliteter-block.js"
   */
  register_block_type('faciliteter/block', array(

    /**
     * Kör följande funktion när blocket
     * ska renderas på webbsidan.
     */
    'render_callback' => 'faciliteter_frontend_block'
  ));
}

// tl;dr
// GET = Är synligt i URLen
// POST = Är inte synlig i URL:en, bara synlig i BODY:n.

function faciliteter_frontend_block($attributes)
{
  // $html_result = "Attributen från Gutenberg-block: {$attributes["facilitet"]}";

  $html_result = "";

  $host = 'localhost';
  $user = 'root';
  $passwd = '';
  $schema = 'hamn';

  $conn = new mysqli($host, $user, $passwd, $schema);

  if ($conn->connect_error) {
    echo ("Anslutning failar: " . $conn->connect_error);
  }

  if ($_POST != null) {
    $req_name = $attributes["facilitet"];
    $req_rating = intval($_POST["facilitet_vote"]);
    $req_ip = '0';

    /**
     * "Frontend" Användaren har skickat en
     * respons till oss via formuläret
     */
    
    // Mata in information till databas...
    $sql_insert_voting = "INSERT INTO faciliteter (name, rating, ip)
    VALUES('$req_name', $req_rating, '$req_ip')";
    
    if ($conn->query($sql_insert_voting) === TRUE) {
      $html_result .= "<p>Du har nu röstat!</p>";
    }
  }


  // Medelvärde uträkning ska finnas här nedanför:
  
  // Skapa en query som ska få tillbaka allting från tabellen.
  $sql_get_all = "SELECT * FROM faciliteter where name='{$attributes["facilitet"]}'";

  $result = $conn->query($sql_get_all);
  $total_rating = 0;
  $total_rows = 0;

  // Jag har fått tillbaka data från databasen.
  if ($result !== FALSE && $result->num_rows > 0) {
    // Visa datan.
    while ($row = $result->fetch_assoc()) {
     $total_rating += intval($row["rating"]);
     $total_rows += 1;
    }
  } else {
    // Fick ingenting tillbaka.
    // $html_result = "<p>Tomt i databasen.</p>";
  }
  $calculated_score = 
    $total_rows > 0 ? round($total_rating / $total_rows, 1) : "Ingen har betygsatt ännu!";
  $html_result .= "<p>Betyg: $calculated_score</p>";

  // Stäng av databasen innan vi returnerar.
  $conn->close();
  return $html_result . "
    <form action='' method='post'>
      <p>
        <label for='vote'>Betygsätt vår {$attributes["facilitet"]}</label>
        <br/>
        <input type='number' min='1' max='5' value='5' name='facilitet_vote' id='facilitet_vote'>
        <button>Skicka</button>
      </p>
    </form>
  ";
}


/**
 * "rest_api_init" körs när REST API:n är igång.
 * Alla REST API:n når man via:
 * http://localhost/wp/wp-json/
 */
add_action("rest_api_init", "faciliteter_init_admin_api");

function faciliteter_init_admin_api()
{
  /**
   * Registrera ett REST API på "faciliteter/v1/admin"
   * Alltså på:
   * http://localhost/wp/wp-json/faciliteter/v1/admin
   */
  register_rest_route('faciliteter/v1', '/admin', array(
    // Denna REST API ska bara kallas via POST
    'methods' => 'POST',
    /**
     * Kör följande funktion när någon kallar på vår REST API.
     */
    'callback' => 'faciliteter_admin_api'
  ));
}

function faciliteter_admin_api($data)
{
  /**
   * $_POST & $_GET via
   * "$data->get_params()"
   */
  $request = $data->get_params();

  $host = 'localhost';
  $user = 'root';
  $passwd = '';
  $schema = 'databas_namn';

  $conn = new mysqli($host, $user, $passwd, $schema);

  // Gör admin saker!

  // Admin panelen vill ha lite data, eftersom de angav ?action=data i URLen.
  if ($request["action"] != null && $request["action"] === "data") {
     $test_sql = "SELECT * FROM boka";
    $result = $conn->query($test_sql);

    $dataToReturn = array();

    while ($row = $result->fetch_assoc()) {
      // Pusha till array-en! Funkar som dataToReturn.push(row) i JS.
      array_push($dataToReturn, $row);
    }

    // Glöm ej att alltid stänga connection till databasen!
    $conn->close();
    return rest_ensure_response($dataToReturn);
  }

  // Gör andra admin saker!
  // ...

  $conn->close(); // Glöm ej att alltid stänga connection till databasen!
  return rest_ensure_response("Hej");
}
