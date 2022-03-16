<?php
/*
  Plugin Name: Boka
  @package Boka
*/

function boka_load_my_block_files()
{
  wp_enqueue_script(
    'boka-block-handle',
    plugin_dir_url(__FILE__) . 'boka-block.js',
    array('wp-blocks', 'wp-i18n', 'wp-editor'),
    null
  );
}

add_action('enqueue_block_editor_assets', 'boka_load_my_block_files');

function boka_register_block()
{
  register_block_type('boka/boka-block', array(
    'render_callback' => 'boka_frontend_block'
  ));
}

// Kör när plugin-et är igång för första gången.
add_action('init', 'boka_register_block');

function boka_frontend_block($attributes)
{
  // GET = Är synligt i URLen
  // POST = Är inte synlig i URLen, bara synlig i BODYn.

  $database_result = "";

  $platser = array();

  for ($i = 0; $i < $attributes["max_spots"]; $i++) {
    $place_plus_one = $i + 1;
    $platser[$i] = "<option value='$i'>$place_plus_one</option>";
  }

  $host = 'localhost';
  $user = 'root';
  $passwd = '';
  $schema = 'hamn';

  $conn = new mysqli($host, $user, $passwd, $schema);

  if ($conn->connect_error) {
    echo ("Anslutning failar: " . $conn->connect_error);
  }

  if ($_POST != null) {
    $req_date = $_POST["boka_date"];
    $req_place = intval($_POST["boka_place"]);
    $req_name = $_POST["boka_name"];
    $req_boat_type = intval($_POST["boka_boat_type"]);
    $req_days = intval($_POST["boka_days"]);

    /**
     * TODO: Gör en if-check som avbryter och skickar
     * tillbaka ett meddelande att platsen är bokad.
     */

    $sql_insert_booking = "INSERT INTO boka (date, place, name, boat_type, days)
        VALUES('$req_date', $req_place, '$req_name', $req_boat_type, $req_days)";

    if ($conn->query($sql_insert_booking) === TRUE) {
      $database_result .= "<p>Du har bokat plats! $req_name</p>";
    }
  }

  $sql_get_all_bookings = "SELECT * FROM boka";

  $get_all_bookings_result = $conn->query($sql_get_all_bookings);

  if ($get_all_bookings_result->num_rows > 0) {
    $database_result .= "<table>";

    while ($row = $get_all_bookings_result->fetch_assoc()) {
      $place_plus_one = intval($row["place"]) + 1;
      $place = intval($row["place"]);

      $platser[$place] = "<option value='{$row["place"]}' disabled>$place_plus_one - Bokad</option>";

      $database_result .= "<tr>"
        . "<td>" . $row["date"] . "</td>"
        . "<td>" . $row["place"] . "</td>"
        . "<td>" . $row["name"] . "</td>"
        . "<td>" . $row["boat_type"] . "</td>"
        . "<td>" . $row["days"] . "</td>"
        . "</tr>";
    }

    $database_result .= "</table>";
  }

  $conn->close();
  return $database_result . '
<form action="" method="post">
  <p>
    <label for="boka_date">Start datum</label>
    <br/>
    <input type="date" name="boka_date" id="boka_date">
  </p>

  <p>
    <label for="boka_place">Plats:</label>
    <br/>
    <select name="boka_place" id="boka_place">'
    . implode($platser) .
    '</select>
  </p>

  <p>
    <label for="boka_name">Båtens namn:</label>
    <br/>
    <input type="text" name="boka_name" id="boka_name">
  </p>

  <p>
    <label for="boka_boat_type">Båt-typ:</label>
    <br/>
    <select name="boka_boat_type" id="boka_boat_type">
      <option value="0">Segelbåt</option>
      <option value="1">Motorbåt</option>
      <option value="2">Roddbåt</option>
      <option value="3">Yacht</option>
      <option value="4">Gummibåt</option>
      <option value="5">Vattenscooter</option>
      <option value="6">Kajak</option>
    </select>
  </p>

  <p>
    <label for="boka_days">Antal dagar:</label>
    <br/>
    <input type="number" min="1" value="1" name="boka_days" id="boka_days">
  </p>

  <p>
    <button>Skicka!</button>
  </p>
</form>
';
}

function boka_init_admin_api()
{
  register_rest_route('boka/v1', '/admin', array(
    // Denna REST API ska bara kallas via POST
    'methods' => 'POST',
    'callback' => 'boka_admin_api',
    /**
     * Om användaren inte är en admin/editor,
     * tillåt inte API:n att fortsätta!
     */
    'permission_callback' => function () {
      return current_user_can('edit_others_pages');
    }
  ));
}

add_action("rest_api_init", "boka_init_admin_api");

// POST request
function boka_admin_api($data)
{
  /**
   * $_POST & $_GET via
   * "$data->get_params()"
   */
  $request = $data->get_params();

  $host = 'localhost';
  $user = 'root';
  $passwd = '';
  $schema = 'hamn';

  $conn = new mysqli($host, $user, $passwd, $schema);

  if ($conn->connect_error) {
    echo ("Anslutning failar: " . $conn->connect_error);
  }

  if ($request["action"] != null && $request["action"] === "data") {
    $sql_get_all = "SELECT * FROM boka";
    $result = $conn->query($sql_get_all);

    $dataToReturn = array();

    while ($row = $result->fetch_assoc()) {
      array_push($dataToReturn, $row);
    }

    $conn->close();
    return rest_ensure_response($dataToReturn);
  }

  // Användaren vill ta bort något?
  if ($request["place"] != null) {
    $place = $request["place"];
    $place_plus_one = intval($place) + 1;
    $sql_delete = "DELETE FROM boka WHERE place=$place";

    $result = $conn->query($sql_delete);

    $dataToReturn = "";

    if ($result === FALSE) {
      $dataToReturn = "Det gick inte så bra att ta bort!";
    } else {
      $dataToReturn = "Tog bort bokning från plats : $place_plus_one!";
    }

    $conn->close();
    return rest_ensure_response(serialize($dataToReturn));
  }

  $conn->close();
  return rest_ensure_response("Hej");
}
