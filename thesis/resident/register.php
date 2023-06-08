<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  /** creating connection to db */
  require_once 'dbcon.php';
  require 'PHPMailer/src/Exception.php';
  require 'PHPMailer/src/PHPMailer.php';
  require 'PHPMailer/src/SMTP.php';

  /** start session */
  session_start();
  
  # for opening modals
  $showModal = 0;
  $checkmodal2 = 0;
  $checkmodal3 = 0;

  # set date
  date_default_timezone_set('Asia/Manila');
  $date = date('Y-m-d');
  $minordate = date('Y-m-d', strtotime($date . ' - 12 years'));

  /** account user validation */
  if(isset($_SESSION['userresident'])){
    header("Location: loginHomepage.php");
  }
  
  #opening choosetype codes
  if(isset($_REQUEST['chooseopen'])){
    $type = $_REQUEST['chooseopen'];
    if($type == 1){
      $showModal = 1;
    }
    if($type == 2){
      $showModal = 5;
    }
  }

  /** resident code validation */
  if(isset($_REQUEST['checkcode'])){
    $resident_code = filter_var(strtolower($_REQUEST['hcode']),FILTER_SANITIZE_STRING);
    /** variable use to decide in the if statement */
    try{
      /** sql query in pdo format that check if the resident code input is already existing in the db */
      $slct_stmt1 = $con->prepare("SELECT resident_code FROM penresident WHERE resident_code = :resident_code LIMIT 1");
      $slct_stmt1->execute([
        ':resident_code' => $resident_code
      ]);
      $row1 = $slct_stmt1->fetch(PDO::FETCH_ASSOC);
      if($slct_stmt1->rowCount() > 0){
        $checkmodal1 = 0;
        $_SESSION['error']['checker4'] = 0;
        $showModal = 2;
      }
      else{
        $type = $_REQUEST['type'];
        $errorMSG = "Invalid resident code.";
        $showModal = 1;
      }
    }
    catch(PDOException $e){
      $pdoError = $e->getMesage();
    }
  }

  /** resident code validation */
  if(isset($_REQUEST['resiid'])){
    $resident_id = filter_var(strtolower($_REQUEST['resid']),FILTER_SANITIZE_STRING);
    /** variable use to decide in the if statement */
    try{
      /** sql query in pdo format that check if the resident code input is already existing in the db */
      $slct_stmt4 = $con->prepare("SELECT resident_id FROM penresident WHERE resident_id = :resident_id LIMIT 1");
      $slct_stmt4->execute([
        ':resident_id' => $resident_id
      ]);
      $row4 = $slct_stmt4->fetch(PDO::FETCH_ASSOC);
      if($slct_stmt4->rowCount() > 0){
        $checkmodal1 = 0;
        $checkmodal3 = 1;
        /** query to get the appropriate data from the database */ 
        $sql1 = "SELECT email, contact, resident_code, birthdate FROM penresident WHERE resident_id = '$resident_id'";
        $d1 =  $con->query($sql1);
        foreach($d1 as $data1){
          $email1 = ($data1['email']);
          $contact1 = ($data1['contact']);
          $resident_code = ($data1['resident_code']);
          $birthdateguar = ($data1['birthdate']);
        }
        $_SESSION['error']['checker4'] = 1;
        $showModal = 2;
      }
      else{
        $errorMSG = "Invalid resident id.";
        $showModal = 5;
      }
    }
    catch(PDOException $e){
      $pdoError = $e->getMesage();
    }
    
  }

  /** adding resident member codes */
  if(isset($_REQUEST['indiadd'])){
    /** getting and filtering data gathered from the html form */
    $family_position = "Member";
    $firstname = ucwords(strtolower(filter_var($_REQUEST['firstname'],FILTER_SANITIZE_STRING)));
    $middlename = ucwords(strtolower(filter_var($_REQUEST['middlename'],FILTER_SANITIZE_STRING)));
    $lastname = ucwords(strtolower(filter_var($_REQUEST['lastname'],FILTER_SANITIZE_STRING)));
    $birthplace = filter_var($_REQUEST['birthplace'],FILTER_SANITIZE_STRING);
    $gender = $_REQUEST['gender'];
    $citizenship = $_REQUEST['citizenship'];
    $civil = $_REQUEST['civil'];
    $checkmodal3 = $_REQUEST['checkmodal3'];
    $checkmodal4 = $_SESSION['error']['checker4'];
    $religion = $_REQUEST['religion'];
    $contact = filter_var($_REQUEST['contact'],FILTER_SANITIZE_NUMBER_INT);
    $email = filter_var(strtolower($_REQUEST['email']),FILTER_SANITIZE_EMAIL);
    $birthdate = $_REQUEST['birthdate'];
    $family_role = $_REQUEST['family_role'];
    $occupation = filter_var($_REQUEST['occupation'],FILTER_SANITIZE_STRING);
    $nature = $_REQUEST['nature'];
    $birthdateguar = $_REQUEST['birthdateguar'];
    $resident_code = $_REQUEST['resident_code'];
    /** checking if all fields are not empty (by setting the $errorMSG variable)  */
    if(empty($firstname) || empty($middlename) || empty($lastname)  || empty($birthplace) || empty($contact) || empty($email) || empty($birthdate) || empty($occupation) || $gender == "none" || $civil == "none"  || $religion == "none" || $family_role == "none" || $birthdate == "none" || $citizenship == "none"){
      $errorMSG = "Please input all requirements.";
    }
    else{
      if(empty($errorMSG)){
        /** try/catch method for the first query */
        try{
          if($checkmodal4 == 1){
            /** query to get the appropriate data from the database */ 
            $sql6 = "SELECT * FROM penresident WHERE resident_code = '$resident_code'";
            $d5 =  $con->query($sql6);
            foreach($d5 as $data5){
              $old7 = ($data5['old']);
              $new7 = ($data5['new']);
              $street7 = ($data5['street']);
              $village7 = ($data5['village']);
              $housetype7 = ($data5['housetype']);
            }
            $old = $old7;
            $new = $new7;
            $street = $street7;
            $village = $village7;
            $housetype = $housetype7;

            /** setting age */
            $date1 = date('Y-m-d');
            $datetime2 = new DateTime($date1);
            $datetime1 = new DateTime($birthdate);
            $difference = $datetime1->diff($datetime2);
            $age = ($difference->y);

            if($_FILES["image"]["error"] == 4){
              $errorMSG = 'Some went wrong. Please try again.';
              $checkmodal1 = 1;
              $showModal = 2;
            }
            else{
              # preparing image variables
              $fileName = $_FILES["image"]["name"];
              $fileSize = $_FILES["image"]["size"];
              $tmpName = $_FILES["image"]["tmp_name"];
              # variable that check and prepare the file extension
              $validImageExtension = ['jpg', 'jpeg', 'png'];
              $imageExtension = explode('.', $fileName);
              $imageExtension = strtolower(end($imageExtension));
              # prepare final image variable
              $newImageName = uniqid();
              $newImageName .= '.' . $imageExtension;
              # check image errors
              if ( !in_array($imageExtension, $validImageExtension) ){
                $errorMSG = 'Invalid image extension. Please try again.';
                $checkmodal1 = 1;
                $showModal = 2;
              }
              else if($fileSize > 1000000){
                $errorMSG = 'Image file size is too big.';
                $checkmodal1 = 1;
                $showModal = 2;
              }
              else{
                move_uploaded_file($tmpName, 'images/' . $newImageName);
                $showModal = 3;
              }
            }
          }
          else{
            /** sql query in pdo format that check if the email input is already existing in the db */
            $slct_stmt2 = $con->prepare("SELECT email FROM penresident WHERE email = :email");
            $slct_stmt2->execute([':email' => $email]);
            $row4 = $slct_stmt2->fetch(PDO::FETCH_ASSOC);

            if(isset($row4['email']) == $email){
              $errorMSG = 'Email already registered.';
              $checkmodal1 = 1;
              $checkmodal2 = 1;
              $showModal = 2;
            }
            else{
              # verifying entered email if it is valid using api key
              $api_key = "a12277e410a640b2a99529005100db48";
              $ch = curl_init();
              curl_setopt_array($ch, [
                CURLOPT_URL => "https://emailvalidation.abstractapi.com/v1?api_key=$api_key&email=$email",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true
              ]);
              $response = curl_exec($ch);

              curl_close($ch);

              $data_key = json_decode($response, true);
              
              if($data_key["deliverability"] === "UNDELIVERABLE"){
                $errorMSG = 'Must be a valid email.';
                $checkmodal1 = 1;
                $checkmodal2 = 1;
                $showModal = 2;
              }
              elseif($data_key["is_disposable_email"]["value"] === true){
                $errorMSG = 'Must be a valid email.';
                $checkmodal1 = 1;
                $checkmodal2 = 1;
                $showModal = 2;
              }
              else{
                /** query to get the appropriate data from the database */ 
                $sql6 = "SELECT * FROM penresident WHERE resident_code = '$resident_code'";
                $d5 =  $con->query($sql6);
                foreach($d5 as $data5){
                  $old7 = ($data5['old']);
                  $new7 = ($data5['new']);
                  $street7 = ($data5['street']);
                  $village7 = ($data5['village']);
                  $housetype7 = ($data5['housetype']);
                }
                $old = $old7;
                $new = $new7;
                $street = $street7;
                $village = $village7;
                $housetype = $housetype7;

                /** setting age */
                $date1 = date('Y-m-d');
                $datetime2 = new DateTime($date1);
                $datetime1 = new DateTime($birthdate);
                $difference = $datetime1->diff($datetime2);
                $age = ($difference->y);

                if($_FILES["image"]["error"] == 4){
                  $errorMSG = 'Some went wrong. Please try again.';
                  $checkmodal1 = 1;
                  $showModal = 2;
                }
                else{
                  # preparing image variables
                  $fileName = $_FILES["image"]["name"];
                  $fileSize = $_FILES["image"]["size"];
                  $tmpName = $_FILES["image"]["tmp_name"];
                  # variable that check and prepare the file extension
                  $validImageExtension = ['jpg', 'jpeg', 'png'];
                  $imageExtension = explode('.', $fileName);
                  $imageExtension = strtolower(end($imageExtension));
                  # prepare final image variable
                  $newImageName = uniqid();
                  $newImageName .= '.' . $imageExtension;
                  # check image errors
                  if ( !in_array($imageExtension, $validImageExtension) ){
                    $errorMSG = 'Invalid image extension. Please try again.';
                    $checkmodal1 = 1;
                    $showModal = 2;
                  }
                  else if($fileSize > 1000000){
                    $errorMSG = 'Image file size is too big.';
                    $checkmodal1 = 1;
                    $showModal = 2;
                  }
                  else{
                    move_uploaded_file($tmpName, 'images/' . $newImageName);
                    $showModal = 3;
                  }
                }
              }
            }
          }
          
        }
        catch(PDOException $e){
          $pdoError = $e->getMesage();
        }
      }
    }
  }
  
  /** reloading census form by retaining previous inputs codes */
  if(isset($_REQUEST['reloadaddhead'])){
    $firstname = $_REQUEST['firstname'];
    $middlename = $_REQUEST['middlename'];
    $lastname = $_REQUEST['lastname'];
    $gender = $_REQUEST['gender'];
    $birthdate = $_REQUEST['birthdate'];
    $birthplace = $_REQUEST['birthplace'];
    $citizenship = $_REQUEST['citizenship'];
    $civil = $_REQUEST['civil'];
    $religion = $_REQUEST['religion'];
    $contact = $_REQUEST['contact'];
    $email = $_REQUEST['email'];
    $occupation = $_REQUEST['occupation'];
    $nature = $_REQUEST['nature'];
    $old = $_REQUEST['old'];
    $new = $_REQUEST['new'];
    $street = $_REQUEST['street'];
    $village = $_REQUEST['village'];
    $housetype = $_REQUEST['housetype'];
    $family_role = $_REQUEST['family_role'];
    $resident_code = $_REQUEST['resident_code'];
    $newImageName = $_REQUEST['newImageName'];
    $_SESSION['error']['checker4'] = 1;
    $checkmodal4 = $_SESSION['error']['checker4'];
    $birthdateguar = $_REQUEST['birthdateguar'];
    $checkmodal3 = $_REQUEST['checkmodal3'];
    unlink("images/".$newImageName);
    $checkmodal1 = 1;
    $showModal = 2;
  }

  /** confirmation of adding household member codes */
  if(isset($_REQUEST['confmemadding'])){
    $resident_code = $_REQUEST['resident_code'];
    $family_position = $_REQUEST['family_position'];
    $firstname = $_REQUEST['firstname'];
    $middlename = $_REQUEST['middlename'];
    $lastname = $_REQUEST['lastname'];
    $gender = $_REQUEST['gender'];
    $birthdate = $_REQUEST['birthdate'];
    $birthplace = $_REQUEST['birthplace'];
    $citizenship = $_REQUEST['citizenship'];
    $civil = $_REQUEST['civil'];
    $religion = $_REQUEST['religion'];
    $contact = $_REQUEST['contact'];
    $email = $_REQUEST['email'];
    $occupation = $_REQUEST['occupation'];
    $nature = $_REQUEST['nature'];
    $old = $_REQUEST['old'];
    $new = $_REQUEST['new'];
    $street = $_REQUEST['street'];
    $village = $_REQUEST['village'];
    $housetype = $_REQUEST['housetype'];
    $family_role = $_REQUEST['family_role'];
    $newImageName = $_REQUEST['newImageName'];
    $status = "Pending";

    /** characters to be used in the resident id */
    $permitted_resid = '012345678901234567890123456789012345678901234567890123456789';

    /** dowhile loop that prevent the duplication of resident id in the db */
    $chk1 = true;
    $cts1 = 0;
    do {
      $cts1++;
      /** creating resident id by shuffling those permitted characters*/
      $resident_id = (substr(str_shuffle($permitted_resid ), 0, 5));
      /** query to check if the produce resident id is already existing in the db */
      $slct_stmt3 = $con->prepare("SELECT resident_id FROM penresident WHERE resident_id = :resident_id");
      $slct_stmt3->execute([':resident_id' => $resident_id]);
      $row5 = $slct_stmt3->fetch(PDO::FETCH_ASSOC);
      if(isset($row5['resident_id']) == $resident_id){
        $check1 = false;
      }
      else{
        $check1 = true;
      }
    } while ($chk1 == false && $cts1 != 20);

    if($chk1){
      /** setting the time of the session */
      $date_reg = date('Y-m-d H:i:s');
      try{
        /** preparing a pdo query that adds values to the db */
        $query1 = $con->prepare("INSERT INTO penresident (resident_id, resident_code, family_position, housetype, old, new, street, village, firstname, middlename, lastname, birthdate, gender, citizenship, birthplace, occupation, civil, religion, nature, contact, email, status, family_role, last_session, verification_id)
        VALUES (:resident_id, :resident_code, :family_position, :housetype, :old, :new, :street, :village, :firstname, :middlename, :lastname, :birthdate, :gender, :citizenship, :birthplace, :occupation, :civil, :religion, :nature, :contact, :email, :status, :family_role, :last_session, :verification_id)");
        /** executing the query */
        if($query1->execute(
          [
          ':resident_id' => $resident_id,
          ':resident_code' => $resident_code,
          ':family_position' => $family_position,
          ':housetype' => $housetype,
          ':old' => $old,
          ':new' => $new,
          ':street' => $street,
          ':village' => $village,
          ':firstname' => $firstname,
          ':middlename' => $middlename,
          ':lastname' => $lastname,
          ':birthdate' => $birthdate,
          ':gender' => $gender,
          ':citizenship' => $citizenship,
          ':birthplace' => $birthplace,
          ':occupation' => $occupation,
          ':civil' => $civil,
          ':religion' => $religion,
          ':nature' => $nature,
          ':contact' => $contact,
          ':email' => $email,
          ':status' => $status,
          ':last_session' => $date_reg,
          ':verification_id' => $newImageName,
          ':family_role' => $family_role
        ]
        )
        ){
          $showModal = 4;
        }
      }
      catch(PDOException $e){
        $pdoError = $e->getMesage();
      }
    }
    else{
      $errorMSG = "The amount of resident is already maxed.";
      $showModal = 2;
    }
  }

  /** reloading page */
  if(isset($_REQUEST['confirmresident'])){
    session_destroy();
    header("Location: register.php");
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <link rel="stylesheet" href="css/signup.css" />
    <title>Sign in & Sign up Form</title>
    <style>
      /* Chrome, Safari, Edge, Opera */
      input::-webkit-outer-spin-button,
      input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }
      .fa-eye{
        position: absolute;
        top: 2%;
        left: 88%; 
        cursor: pointer;
      }
    </style>
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <?php
    if($checkmodal2 == 1){
      echo '<script>
      $(document).ready(function(){
          $("#addindividual").on("shown.bs.modal", function(){
              $(this).find("#emailrel").focus();
          });
      });
      </script>';
    }
    ?>
    
  </head>
  <body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="login.php" method='post' class="sign-in-form">
            <h2 class="title text-center">BECOME A RESIDENT!</h2>
          </form>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3>Concepcion Dos</h3>
            <p>
              There's no greater challenge and there's no greater honor than to be in public service. - Condoleezza Rice
            </p>
          </div>
          <img src="img/login.svg" class="image" alt="" />
        </div>
        <div class="panel right-panel">
          <div class="content">
            <h3>Registered already?</h3>
            <p>
              Come and enjoy our community.
            </p>
            <button class="btn transparent text-white" id="sign-in-btn">
              Sign in
            </button>
          </div>
          <img src="img/regis.svg" class="image" alt="" />
        </div>
      </div>
    </div>

    <!-- requirements pop-up modal -->
    <div class="modal fade" id="registermodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title" id="staticBackdropLabel">Registration Requirements</h5>
          </div>
          <div class="modal-body">
            <p class="text-start fs-5 ps-3">Household Code (Code can be retrieve from a head of a household that is registered on our barangay.)</p>
            <p class="text-start fs-5 ps-3">Picture of your valid I.D.(e.g. School ID, Company/Office ID, Voter’s ID and etc.)</p>
            <p class="text-start fs-5 ps-3">For minors: Please provide a clear picture of their Birth Certificate.</p>
          </div>
          <form action="register.php" method="post">
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#datapri">Continue</button>
            <a href="login.php"><button type="button" class="btn btn-secondary">Cancel</button></a>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- requirements pop-up modal end -->

    <!-- data privacy pop-up modal -->
    <div class="modal fade" id="datapri" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title" id="staticBackdropLabel">Data Privacy Act of 2012</h5>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <p class="text-start fs-5 ps-3 pe-3">Declaration of Policy. – It is the policy of the State to protect the fundamental human right of privacy, of communication while ensuring free flow of information to promote innovation and growth. The State recognizes the vital role of information and communications technology in nation-building and its inherent obligation to ensure that personal information in information and communications systems in the government and in the private sector are secured and protected.</p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <p class="text-start fs-5 ps-3 pe-3">By agreeing, I hereby provided correct  information and give the barangay the permission to use it with my consent.</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#choosetype">I Agree</button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#registermodal">I Disagree</button>
          </div>
        </div>
      </div>
    </div>
    <!-- data privacy pop-up modal end -->

    <!-- choosing type pop-up modal -->
    <div class="modal fade" id="choosetype" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title" id="staticBackdropLabel">Adding Resident</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-toggle="modal" data-bs-target="#registermodal"></button>
          </div>
          <div class="modal-body">
          <form action="register.php" method="post">
            <div class="row justify-content-center">
              <div class="col-md-6">
                <img src="images/adult.jpg" class="img-thumbnail rounded mx-auto d-block" alt="...">
                <p class="text-center"><button type="submit" class="btn btn-primary" name="chooseopen" value="1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Personal Account&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</button></p>
              </div>
              <div class="col-md-6">
                <img src="images/minor.jpg" class="img-thumbnail rounded mx-auto d-block" alt="...">
                <p class="text-center"><button type="submit" class="btn btn-primary" name="chooseopen" value="2">Family Member(Minor)</button></p>
              </div>
            </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- choosing type pop-up modal -->

    <!-- entering code pop-up modal -->
    <div class="modal fade" id="censusmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <?php
            if($type == 1){
              echo'<h5 class="modal-title" id="staticBackdropLabel">Adding Personal Account</h5>';
            }
            else{
              echo'<h5 class="modal-title" id="staticBackdropLabel">Adding Minor Account Account</h5>';
            }
            ?> 
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <?php
                  if(isset($errorMSG)){
                    echo '<div class="alert alert-warning" role="alert">
                            '.$errorMSG.'
                          </div>';
                  }
                ?>
              </div>
            </div>
            <form action="register.php" method="post">
            <div class="row">
              <div class="col-md-12">
                <label for="hcode" class="form-label">Household Code (Provided by the Household Leader)</label>
                <input type="text" class="form-control" id="hcode" name="hcode" placeholder="Enter valid household code..." required>
              </div>
            </div>
            <input type="hidden" name="type" value="<?=$type?>">
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="checkcode">Submit</button>
            <a href="register.php"><button type="button" class="btn btn-secondary" name="confirmresident">Cancel</button></a>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- entering code pop-up modal -->

    <!-- entering parent resident id pop-up modal -->
    <div class="modal fade" id="censusmodalminor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title" id="staticBackdropLabel">Adding Minor Account</h5>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <?php
                  if(isset($errorMSG)){
                    echo '<div class="alert alert-warning" role="alert">
                            '.$errorMSG.'
                          </div>';
                  }
                ?>
              </div>
            </div>
            <form action="register.php" method="post">
            <div class="row">
              <div class="col-md-12">
                <label for="resid" class="form-label">Guardian Resident ID</label>
                <input type="text" class="form-control" id="resid" name="resid" placeholder="Enter valid resident id..." required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="resiid">Submit</button>
            <a href="register.php"><button type="button" class="btn btn-secondary" name="confirmresident">Cancel</button></a>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- entering parent resident id pop-up modal end -->

    <!-- adding individual pop-up modal 2 -->
    <div class="modal fade" id="addindividual" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title">Census Form</h5>
          </div>
          <div class="modal-body">
            <form action="register.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="resident_code" value="<?=$resident_code?>">
              <input type="hidden" name="birthdateguar" value="<?=$birthdateguar?>">
              <input type="hidden" name="checkmodal3" value="<?=$checkmodal3?>">
              <?php
                if(isset($errorMSG)){
                  echo '<div class="alert alert-warning" role="alert">
                          '.$errorMSG.'
                        </div>';
                }
                if(isset($errorMSG2)){
                  echo "<div class='alert alert-danger d-flex align-items-center' role='alert'>
                  <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Danger:'><use xlink:href='#exclamation-triangle-fill'/></svg>
                  <div>"
                    .$errorMSG2.
                  "</div>
                </div>";
                }
                if(isset($errorMSG3)){
                  echo "<div class='alert alert-danger d-flex align-items-center' role='alert'>
                  <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Danger:'><use xlink:href='#exclamation-triangle-fill'/></svg>
                  <div>"
                    .$errorMSG3.
                  "</div>
                </div>";
                }
                if(isset($errorMSG4)){
                  echo "<div class='alert alert-danger d-flex align-items-center' role='alert'>
                  <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Danger:'><use xlink:href='#exclamation-triangle-fill'/></svg>
                  <div>"
                    .$errorMSG4.
                  "</div>
                </div>";
                }

                if($checkmodal1 == 1){
                  echo '<input type="hidden" name="resident_code" value="'.$resident_code.'">
                        <div class="row">
                          <div class="col-md-12">
                            <label for="firstname" class="form-label">Firstname</label>
                            <input type="text" class="form-control text-start" name="firstname" id="firstname" value="'.$firstname.'" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <label for="middlename" class="form-label">Middlename</label>
                            <input type="text" class="form-control" name="middlename" id="middlename" value="'.$middlename.'" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <label for="lastname" class="form-label">Lastname</label>
                            <input type="text" class="form-control" name="lastname" id="lastname" value="'.$lastname.'" required>
                          </div>
                        </div>
                        <div class="row">';
                  if($gender == 'Male'){
                    echo '<div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" aria-label="Default select example" name="gender" id="gender" required>
                              <option >Select</option>
                              <option value="Male" selected>Male</option>
                              <option value="Female">Female</option>
                            </select>
                          </div>';
                  }
                  else{
                    echo '<div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" aria-label="Default select example" name="gender" id="gender" required>
                              <option >Select</option>
                              <option value="Male">Male</option>
                              <option value="Female" selected>Female</option>
                            </select>
                          </div>';
                  }
                  if($checkmodal4 == 1){
                    if($checkmodal3 == 1){
                      echo '<div class="col-md-6">
                                <label for="birthdate" class="form-label">Birthdate</label>
                                <input type="date" class="form-select" id="birthdate" name="birthdate" min="'.$minordate.'" max="'.$date.'" value="'.$birthdate.'" required>
                              </div>
                            </div>';
                    }
                    else{
                      echo '<div class="col-md-6">
                                <label for="birthdate" class="form-label">Birthdate</label>
                                <input type="date" class="form-select" id="birthdate" name="birthdate" min="1880-01-01" max="'.$date.'" value="'.$birthdate.'" required>
                              </div>
                            </div>';
                    }
                  }
                  else{
                    echo '<div class="col-md-6">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-select" id="birthdate" name="birthdate" min="1890-01-01" max="'.$date.'" value="'.$birthdate.'" required>
                          </div>
                        </div>';
                  }   
                  echo '<div class="row">
                        <div class="col-md-12">
                          <label for="birthplace" class="form-label">Birthplace</label>
                          <input type="text" class="form-control" name="birthplace" id="birthplace" value="'.$birthplace.'" required>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <label for="citizenship" class="form-label">Citizenship</label>
                          <input class="form-control" list="lists" id="citizenship" name="citizenship" value="'.$citizenship.'" required>
                          <datalist id="lists">
                            <option selected value="Filipino">Filipino</option>
                            <option value="Afghanistan">Afghanistan</option>
                            <option value="Åland Islands">Åland Islands</option>
                            <option value="Albania">Albania</option>
                            <option value="Algeria">Algeria</option>
                            <option value="American Samoa">American Samoa</option>
                            <option value="Andorra">Andorra</option>
                            <option value="Angola">Angola</option>
                            <option value="Anguilla">Anguilla</option>
                            <option value="Antarctica">Antarctica</option>
                            <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Armenia">Armenia</option>
                            <option value="Aruba">Aruba</option>
                            <option value="Australia">Australia</option>
                            <option value="Austria">Austria</option>
                            <option value="Azerbaijan">Azerbaijan</option>
                            <option value="Bahamas">Bahamas</option>
                            <option value="Bahrain">Bahrain</option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Barbados">Barbados</option>
                            <option value="Belarus">Belarus</option>
                            <option value="Belgium">Belgium</option>
                            <option value="Belize">Belize</option>
                            <option value="Benin">Benin</option>
                            <option value="Bermuda">Bermuda</option>
                            <option value="Bhutan">Bhutan</option>
                            <option value="Bolivia">Bolivia</option>
                            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                            <option value="Botswana">Botswana</option>
                            <option value="Bouvet Island">Bouvet Island</option>
                            <option value="Brazil">Brazil</option>
                            <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                            <option value="Brunei Darussalam">Brunei Darussalam</option>
                            <option value="Bulgaria">Bulgaria</option>
                            <option value="Burkina Faso">Burkina Faso</option>
                            <option value="Burundi">Burundi</option>
                            <option value="Cambodia">Cambodia</option>
                            <option value="Cameroon">Cameroon</option>
                            <option value="Canada">Canada</option>
                            <option value="Cape Verde">Cape Verde</option>
                            <option value="Cayman Islands">Cayman Islands</option>
                            <option value="Central African Republic">Central African Republic</option>
                            <option value="Chad">Chad</option>
                            <option value="Chile">Chile</option>
                            <option value="China">China</option>
                            <option value="Christmas Island">Christmas Island</option>
                            <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Comoros">Comoros</option>
                            <option value="Congo">Congo</option>
                            <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                            <option value="Cook Islands">Cook Islands</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Cote D\'ivoire">Cote D\'ivoire</option>
                            <option value="Croatia">Croatia</option>
                            <option value="Cuba">Cuba</option>
                            <option value="Cyprus">Cyprus</option>
                            <option value="Czech Republic">Czech Republic</option>
                            <option value="Denmark">Denmark</option>
                            <option value="Djibouti">Djibouti</option>
                            <option value="Dominica">Dominica</option>
                            <option value="Dominican Republic">Dominican Republic</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="Egypt">Egypt</option>
                            <option value="El Salvador">El Salvador</option>
                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                            <option value="Eritrea">Eritrea</option>
                            <option value="Estonia">Estonia</option>
                            <option value="Ethiopia">Ethiopia</option>
                            <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                            <option value="Faroe Islands">Faroe Islands</option>
                            <option value="Fiji">Fiji</option>
                            <option value="Finland">Finland</option>
                            <option value="France">France</option>
                            <option value="French Guiana">French Guiana</option>
                            <option value="French Polynesia">French Polynesia</option>
                            <option value="French Southern Territories">French Southern Territories</option>
                            <option value="Gabon">Gabon</option>
                            <option value="Gambia">Gambia</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Germany">Germany</option>
                            <option value="Ghana">Ghana</option>
                            <option value="Gibraltar">Gibraltar</option>
                            <option value="Greece">Greece</option>
                            <option value="Greenland">Greenland</option>
                            <option value="Grenada">Grenada</option>
                            <option value="Guadeloupe">Guadeloupe</option>
                            <option value="Guam">Guam</option>
                            <option value="Guatemala">Guatemala</option>
                            <option value="Guernsey">Guernsey</option>
                            <option value="Guinea">Guinea</option>
                            <option value="Guinea-bissau">Guinea-bissau</option>
                            <option value="Guyana">Guyana</option>
                            <option value="Haiti">Haiti</option>
                            <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                            <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                            <option value="Honduras">Honduras</option>
                            <option value="Hong Kong">Hong Kong</option>
                            <option value="Hungary">Hungary</option>
                            <option value="Iceland">Iceland</option>
                            <option value="India">India</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                            <option value="Iraq">Iraq</option>
                            <option value="Ireland">Ireland</option>
                            <option value="Isle of Man">Isle of Man</option>
                            <option value="Israel">Israel</option>
                            <option value="Italy">Italy</option>
                            <option value="Jamaica">Jamaica</option>
                            <option value="Japan">Japan</option>
                            <option value="Jersey">Jersey</option>
                            <option value="Jordan">Jordan</option>
                            <option value="Kazakhstan">Kazakhstan</option>
                            <option value="Kenya">Kenya</option>
                            <option value="Kiribati">Kiribati</option>
                            <option value="Korea, Democratic People\'s Republic of">Korea, Democratic People\'s Republic of</option>
                            <option value="Korea, Republic of">Korea, Republic of</option>
                            <option value="Kuwait">Kuwait</option>
                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                            <option value="Lao People\'s Democratic Republic">Lao People\'s Democratic Republic</option>
                            <option value="Latvia">Latvia</option>
                            <option value="Lebanon">Lebanon</option>
                            <option value="Lesotho">Lesotho</option>
                            <option value="Liberia">Liberia</option>
                            <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                            <option value="Liechtenstein">Liechtenstein</option>
                            <option value="Lithuania">Lithuania</option>
                            <option value="Luxembourg">Luxembourg</option>
                            <option value="Macao">Macao</option>
                            <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                            <option value="Madagascar">Madagascar</option>
                            <option value="Malawi">Malawi</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Maldives">Maldives</option>
                            <option value="Mali">Mali</option>
                            <option value="Malta">Malta</option>
                            <option value="Marshall Islands">Marshall Islands</option>
                            <option value="Martinique">Martinique</option>
                            <option value="Mauritania">Mauritania</option>
                            <option value="Mauritius">Mauritius</option>
                            <option value="Mayotte">Mayotte</option>
                            <option value="Mexico">Mexico</option>
                            <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                            <option value="Moldova, Republic of">Moldova, Republic of</option>
                            <option value="Monaco">Monaco</option>
                            <option value="Mongolia">Mongolia</option>
                            <option value="Montenegro">Montenegro</option>
                            <option value="Montserrat">Montserrat</option>
                            <option value="Morocco">Morocco</option>
                            <option value="Mozambique">Mozambique</option>
                            <option value="Myanmar">Myanmar</option>
                            <option value="Namibia">Namibia</option>
                            <option value="Nauru">Nauru</option>
                            <option value="Nepal">Nepal</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="Netherlands Antilles">Netherlands Antilles</option>
                            <option value="New Caledonia">New Caledonia</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="Nicaragua">Nicaragua</option>
                            <option value="Niger">Niger</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="Niue">Niue</option>
                            <option value="Norfolk Island">Norfolk Island</option>
                            <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                            <option value="Norway">Norway</option>
                            <option value="Oman">Oman</option>
                            <option value="Pakistan">Pakistan</option>
                            <option value="Palau">Palau</option>
                            <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                            <option value="Panama">Panama</option>
                            <option value="Papua New Guinea">Papua New Guinea</option>
                            <option value="Paraguay">Paraguay</option>
                            <option value="Peru">Peru</option>
                            <option value="Pitcairn">Pitcairn</option>
                            <option value="Poland" selected>Poland</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Puerto Rico">Puerto Rico</option>
                            <option value="Qatar">Qatar</option>
                            <option value="Reunion">Reunion</option>
                            <option value="Romania">Romania</option>
                            <option value="Russian Federation">Russian Federation</option>
                            <option value="Rwanda">Rwanda</option>
                            <option value="Saint Helena">Saint Helena</option>
                            <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                            <option value="Saint Lucia">Saint Lucia</option>
                            <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                            <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                            <option value="Samoa">Samoa</option>
                            <option value="San Marino">San Marino</option>
                            <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                            <option value="Saudi Arabia">Saudi Arabia</option>
                            <option value="Senegal">Senegal</option>
                            <option value="Serbia">Serbia</option>
                            <option value="Seychelles">Seychelles</option>
                            <option value="Sierra Leone">Sierra Leone</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Slovakia">Slovakia</option>
                            <option value="Slovenia">Slovenia</option>
                            <option value="Solomon Islands">Solomon Islands</option>
                            <option value="Somalia">Somalia</option>
                            <option value="South Africa">South Africa</option>
                            <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                            <option value="Spain">Spain</option>
                            <option value="Sri Lanka">Sri Lanka</option>
                            <option value="Sudan">Sudan</option>
                            <option value="Suriname">Suriname</option>
                            <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                            <option value="Swaziland">Swaziland</option>
                            <option value="Sweden">Sweden</option>
                            <option value="Switzerland">Switzerland</option>
                            <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                            <option value="Taiwan">Taiwan</option>
                            <option value="Tajikistan">Tajikistan</option>
                            <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Timor-leste">Timor-leste</option>
                            <option value="Togo">Togo</option>
                            <option value="Tokelau">Tokelau</option>
                            <option value="Tonga">Tonga</option>
                            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                            <option value="Tunisia">Tunisia</option>
                            <option value="Turkey">Turkey</option>
                            <option value="Turkmenistan">Turkmenistan</option>
                            <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                            <option value="Tuvalu">Tuvalu</option>
                            <option value="Uganda">Uganda</option>
                            <option value="Ukraine">Ukraine</option>
                            <option value="United Arab Emirates">United Arab Emirates</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                            <option value="Uruguay">Uruguay</option>
                            <option value="Uzbekistan">Uzbekistan</option>
                            <option value="Vanuatu">Vanuatu</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Viet Nam">Viet Nam</option>
                            <option value="Virgin Islands, British">Virgin Islands, British</option>
                            <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                            <option value="Wallis and Futuna">Wallis and Futuna</option>
                            <option value="Western Sahara">Western Sahara</option>
                            <option value="Yemen">Yemen</option>
                            <option value="Zambia">Zambia</option>
                            <option value="Zimbabwe">Zimbabwe</option>
                          </datalist>
                        </div>
                      </div>';
                  if($civil == 'Single'){
                    echo '<div class="row">
                            <div class="col-md-4">
                              <label for="civil" class="form-label">Civil Status</label>
                              <select class="form-select" aria-label="Default select example" name="civil" id="civil" required>
                                <option value="Single" selected>Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                              </select>
                            </div>';
                  }
                  elseif($civil == 'Married'){
                    echo '<div class="row">
                            <div class="col-md-4">
                              <label for="civil" class="form-label">Civil Status</label>
                              <select class="form-select" aria-label="Default select example" name="civil" id="civil" required>
                                <option value="Single">Single</option>
                                <option value="Married" selected>Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                              </select>
                            </div>';
                  }
                  elseif($civil == 'Divorced'){
                    echo '<div class="row">
                            <div class="col-md-4">
                              <label for="civil" class="form-label">Civil Status</label>
                              <select class="form-select" aria-label="Default select example" name="civil" id="civil" required>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced" selected>Divorced</option>
                                <option value="Widowed">Widowed</option>
                              </select>
                            </div>';
                  }
                  else{
                    echo '<div class="row">
                            <div class="col-md-4">
                              <label for="civil" class="form-label">Civil Status</label>
                              <select class="form-select" aria-label="Default select example" name="civil" id="civil" required>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed" selected>Widowed</option>
                              </select>
                            </div>';
                  }
                  echo '<div class="col-md-8">
                          <label for="religion" class="form-label">Religion</label>
                          <input class="form-control" list="lists3" id="religion" name="religion" value="'.$religion.'" required>
                          <datalist id="lists3">
                            <option value="" hidden>Select</option>
                            <option value="Roman Catholic">Roman Catholic</option>
                            <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                            <option value="Jehova\'s Witnesses">Jehova\'s Witnesses</option>
                            <option value="Born Again">Born Again</option>
                          </datalist>
                        </div>
                      </div>';
                  if($checkmodal4 == 1){
                    if($checkmodal3 == 1){
                      echo '<input type="hidden" id="contact" name="contact"value="'.$contact.'">
                          <input type="hidden" name="email" id="emailrel" value="'.$email.'">
                          <input type="hidden"name="occupation" id="occupation" value="None">
                          <input type="hidden"name="nature" id="nature" value="None">';
                    }
                    else{
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="contact" class="form-label">Contact #(e.g +63 9*********)</label>
                                <div class="input-group">
                                  <span class="input-group-text">+63</span>
                                  <input type="tel" class="form-control" id="contact" name="contact" pattern="[9]{1}[0-9]{9}" value="'.$contact.'" required>
                                </div>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-12">
                                <label for="emailrel" class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" id="emailrel" value="'.$email.'" required>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-12">
                                <label for="occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control" name="occupation" id="occupation" value="'.$occupation.'" required>
                              </div>
                            </div>';
                      if($nature == 'Architecture and Engineering'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering" selected>Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                      elseif($nature == 'Arts, Culture and Entertainment'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment" selected>Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                      elseif($nature == 'Business, Management and Administration'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration" selected>Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                      elseif($nature == 'Communications'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications" selected>Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                      elseif($nature == 'Community and Social Services'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services" selected>Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                      elseif($nature == 'Education'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education" selected>Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Science and Technology'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology" selected>Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Installation, Repair and Maintenance'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance" selected>Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Farming, Fishing and Forestry'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry" selected>Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Government'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government" selected>Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Health and Medicine'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine" selected>Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Law and Public Policy'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy" selected>Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      elseif($nature == 'Sales'){
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales" selected>Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      } 
                      else{
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None" selected>None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                    }
                  }
                  else{
                    echo '<div class="row">
                            <div class="col-md-12">
                              <label for="contact" class="form-label">Contact #(e.g +63 9*********)</label>
                              <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" class="form-control" id="contact" name="contact" pattern="[9]{1}[0-9]{9}" value="'.$contact.'" required>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-12">
                              <label for="emailrel" class="form-label">Email Address</label>
                              <input type="email" class="form-control" name="email" id="emailrel" value="'.$email.'" required>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-12">
                              <label for="occupation" class="form-label">Occupation</label>
                              <input type="text" class="form-control" name="occupation" id="occupation" value="'.$occupation.'" required>
                            </div>
                          </div>';
                    if($nature == 'Architecture and Engineering'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering" selected>Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($nature == 'Arts, Culture and Entertainment'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment" selected>Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($nature == 'Business, Management and Administration'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration" selected>Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($nature == 'Communications'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications" selected>Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($nature == 'Community and Social Services'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services" selected>Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($nature == 'Education'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education" selected>Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Science and Technology'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology" selected>Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Installation, Repair and Maintenance'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance" selected>Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Farming, Fishing and Forestry'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry" selected>Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Government'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government" selected>Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Health and Medicine'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine" selected>Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Law and Public Policy'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy" selected>Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    elseif($nature == 'Sales'){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales" selected>Sales</option>
                                  <option value="None">None</option>
                                </select>
                              </div>
                            </div>';
                    } 
                    else{
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="nature" class="form-label">Nature of work</label>
                                <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                  <option value="Architecture and Engineering">Architecture and Engineering</option>
                                  <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                  <option value="Business, Management and Administration">Business, Management and Administration</option>
                                  <option value="Communications">Communications</option>
                                  <option value="Community and Social Services">Community and Social Services</option>
                                  <option value="Education">Education</option>
                                  <option value="Science and Technology">Science and Technology</option>
                                  <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                  <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                  <option value="Government">Government</option>
                                  <option value="Health and Medicine">Health and Medicine</option>
                                  <option value="Law and Public Policy">Law and Public Policy</option>
                                  <option value="Sales">Sales</option>
                                  <option value="None" selected>None</option>
                                </select>
                              </div>
                            </div>';
                    }
                  }
                }
                else{
                  echo '<div class="row">
                          <div class="col-md-12">
                              <label for="firstname" class="form-label">Firstname</label>
                              <input type="text" class="form-control" name="firstname" id="firstname" placeholder="Firstname..." required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                              <label for="middlename" class="form-label">Middlename</label>
                              <input type="text" class="form-control" name="middlename" id="middlename" placeholder="Middlename..." required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                              <label for="lastname" class="form-label">Lastname</label>
                              <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Lastname..." required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" aria-label="Default select example" name="gender" id="gender" required>
                              <option value="" hidden>Select</option>
                              <option value="Male">Male</option>
                              <option value="Female">Female</option>
                            </select>
                          </div>';
                    if($checkmodal3 == 1){
                      echo '<div class="col-md-6">
                              <label for="birthdate" class="form-label">Birthdate</label>
                              <input type="date" class="form-select" id="birthdate" name="birthdate" min="'.$minordate.'" max="'.$date.'" required>
                            </div>';
                    }
                    else{
                      echo '<div class="col-md-6">
                              <label for="birthdate" class="form-label">Birthdate</label>
                              <input type="date" class="form-select" id="birthdate" name="birthdate" min="1890-01-01" max="'.$date.'" required>
                            </div>';
                    }      
                  echo '</div>
                        <div class="row">
                          <div class="col-md-12">
                            <label for="birthplace" class="form-label">Birthplace</label>
                            <input type="text" class="form-control" name="birthplace" id="birthplace" placeholder="Birthplace..." required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <label for="citizenship" class="form-label">Citizenship</label>
                            <input class="form-control" list="lists" id="citizenship" name="citizenship" required>
                            <datalist id="lists">
                              <option selected value="Filipino">Filipino</option>
                              <option value="Afghanistan">Afghanistan</option>
                              <option value="Åland Islands">Åland Islands</option>
                              <option value="Albania">Albania</option>
                              <option value="Algeria">Algeria</option>
                              <option value="American Samoa">American Samoa</option>
                              <option value="Andorra">Andorra</option>
                              <option value="Angola">Angola</option>
                              <option value="Anguilla">Anguilla</option>
                              <option value="Antarctica">Antarctica</option>
                              <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                              <option value="Argentina">Argentina</option>
                              <option value="Armenia">Armenia</option>
                              <option value="Aruba">Aruba</option>
                              <option value="Australia">Australia</option>
                              <option value="Austria">Austria</option>
                              <option value="Azerbaijan">Azerbaijan</option>
                              <option value="Bahamas">Bahamas</option>
                              <option value="Bahrain">Bahrain</option>
                              <option value="Bangladesh">Bangladesh</option>
                              <option value="Barbados">Barbados</option>
                              <option value="Belarus">Belarus</option>
                              <option value="Belgium">Belgium</option>
                              <option value="Belize">Belize</option>
                              <option value="Benin">Benin</option>
                              <option value="Bermuda">Bermuda</option>
                              <option value="Bhutan">Bhutan</option>
                              <option value="Bolivia">Bolivia</option>
                              <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                              <option value="Botswana">Botswana</option>
                              <option value="Bouvet Island">Bouvet Island</option>
                              <option value="Brazil">Brazil</option>
                              <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                              <option value="Brunei Darussalam">Brunei Darussalam</option>
                              <option value="Bulgaria">Bulgaria</option>
                              <option value="Burkina Faso">Burkina Faso</option>
                              <option value="Burundi">Burundi</option>
                              <option value="Cambodia">Cambodia</option>
                              <option value="Cameroon">Cameroon</option>
                              <option value="Canada">Canada</option>
                              <option value="Cape Verde">Cape Verde</option>
                              <option value="Cayman Islands">Cayman Islands</option>
                              <option value="Central African Republic">Central African Republic</option>
                              <option value="Chad">Chad</option>
                              <option value="Chile">Chile</option>
                              <option value="China">China</option>
                              <option value="Christmas Island">Christmas Island</option>
                              <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                              <option value="Colombia">Colombia</option>
                              <option value="Comoros">Comoros</option>
                              <option value="Congo">Congo</option>
                              <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                              <option value="Cook Islands">Cook Islands</option>
                              <option value="Costa Rica">Costa Rica</option>
                              <option value="Cote D\'ivoire">Cote D\'ivoire</option>
                              <option value="Croatia">Croatia</option>
                              <option value="Cuba">Cuba</option>
                              <option value="Cyprus">Cyprus</option>
                              <option value="Czech Republic">Czech Republic</option>
                              <option value="Denmark">Denmark</option>
                              <option value="Djibouti">Djibouti</option>
                              <option value="Dominica">Dominica</option>
                              <option value="Dominican Republic">Dominican Republic</option>
                              <option value="Ecuador">Ecuador</option>
                              <option value="Egypt">Egypt</option>
                              <option value="El Salvador">El Salvador</option>
                              <option value="Equatorial Guinea">Equatorial Guinea</option>
                              <option value="Eritrea">Eritrea</option>
                              <option value="Estonia">Estonia</option>
                              <option value="Ethiopia">Ethiopia</option>
                              <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                              <option value="Faroe Islands">Faroe Islands</option>
                              <option value="Fiji">Fiji</option>
                              <option value="Finland">Finland</option>
                              <option value="France">France</option>
                              <option value="French Guiana">French Guiana</option>
                              <option value="French Polynesia">French Polynesia</option>
                              <option value="French Southern Territories">French Southern Territories</option>
                              <option value="Gabon">Gabon</option>
                              <option value="Gambia">Gambia</option>
                              <option value="Georgia">Georgia</option>
                              <option value="Germany">Germany</option>
                              <option value="Ghana">Ghana</option>
                              <option value="Gibraltar">Gibraltar</option>
                              <option value="Greece">Greece</option>
                              <option value="Greenland">Greenland</option>
                              <option value="Grenada">Grenada</option>
                              <option value="Guadeloupe">Guadeloupe</option>
                              <option value="Guam">Guam</option>
                              <option value="Guatemala">Guatemala</option>
                              <option value="Guernsey">Guernsey</option>
                              <option value="Guinea">Guinea</option>
                              <option value="Guinea-bissau">Guinea-bissau</option>
                              <option value="Guyana">Guyana</option>
                              <option value="Haiti">Haiti</option>
                              <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                              <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                              <option value="Honduras">Honduras</option>
                              <option value="Hong Kong">Hong Kong</option>
                              <option value="Hungary">Hungary</option>
                              <option value="Iceland">Iceland</option>
                              <option value="India">India</option>
                              <option value="Indonesia">Indonesia</option>
                              <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                              <option value="Iraq">Iraq</option>
                              <option value="Ireland">Ireland</option>
                              <option value="Isle of Man">Isle of Man</option>
                              <option value="Israel">Israel</option>
                              <option value="Italy">Italy</option>
                              <option value="Jamaica">Jamaica</option>
                              <option value="Japan">Japan</option>
                              <option value="Jersey">Jersey</option>
                              <option value="Jordan">Jordan</option>
                              <option value="Kazakhstan">Kazakhstan</option>
                              <option value="Kenya">Kenya</option>
                              <option value="Kiribati">Kiribati</option>
                              <option value="Korea, Democratic People\'s Republic of">Korea, Democratic People\'s Republic of</option>
                              <option value="Korea, Republic of">Korea, Republic of</option>
                              <option value="Kuwait">Kuwait</option>
                              <option value="Kyrgyzstan">Kyrgyzstan</option>
                              <option value="Lao People\'s Democratic Republic">Lao People\'s Democratic Republic</option>
                              <option value="Latvia">Latvia</option>
                              <option value="Lebanon">Lebanon</option>
                              <option value="Lesotho">Lesotho</option>
                              <option value="Liberia">Liberia</option>
                              <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                              <option value="Liechtenstein">Liechtenstein</option>
                              <option value="Lithuania">Lithuania</option>
                              <option value="Luxembourg">Luxembourg</option>
                              <option value="Macao">Macao</option>
                              <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                              <option value="Madagascar">Madagascar</option>
                              <option value="Malawi">Malawi</option>
                              <option value="Malaysia">Malaysia</option>
                              <option value="Maldives">Maldives</option>
                              <option value="Mali">Mali</option>
                              <option value="Malta">Malta</option>
                              <option value="Marshall Islands">Marshall Islands</option>
                              <option value="Martinique">Martinique</option>
                              <option value="Mauritania">Mauritania</option>
                              <option value="Mauritius">Mauritius</option>
                              <option value="Mayotte">Mayotte</option>
                              <option value="Mexico">Mexico</option>
                              <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                              <option value="Moldova, Republic of">Moldova, Republic of</option>
                              <option value="Monaco">Monaco</option>
                              <option value="Mongolia">Mongolia</option>
                              <option value="Montenegro">Montenegro</option>
                              <option value="Montserrat">Montserrat</option>
                              <option value="Morocco">Morocco</option>
                              <option value="Mozambique">Mozambique</option>
                              <option value="Myanmar">Myanmar</option>
                              <option value="Namibia">Namibia</option>
                              <option value="Nauru">Nauru</option>
                              <option value="Nepal">Nepal</option>
                              <option value="Netherlands">Netherlands</option>
                              <option value="Netherlands Antilles">Netherlands Antilles</option>
                              <option value="New Caledonia">New Caledonia</option>
                              <option value="New Zealand">New Zealand</option>
                              <option value="Nicaragua">Nicaragua</option>
                              <option value="Niger">Niger</option>
                              <option value="Nigeria">Nigeria</option>
                              <option value="Niue">Niue</option>
                              <option value="Norfolk Island">Norfolk Island</option>
                              <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                              <option value="Norway">Norway</option>
                              <option value="Oman">Oman</option>
                              <option value="Pakistan">Pakistan</option>
                              <option value="Palau">Palau</option>
                              <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                              <option value="Panama">Panama</option>
                              <option value="Papua New Guinea">Papua New Guinea</option>
                              <option value="Paraguay">Paraguay</option>
                              <option value="Peru">Peru</option>
                              <option value="Pitcairn">Pitcairn</option>
                              <option value="Poland" selected>Poland</option>
                              <option value="Portugal">Portugal</option>
                              <option value="Puerto Rico">Puerto Rico</option>
                              <option value="Qatar">Qatar</option>
                              <option value="Reunion">Reunion</option>
                              <option value="Romania">Romania</option>
                              <option value="Russian Federation">Russian Federation</option>
                              <option value="Rwanda">Rwanda</option>
                              <option value="Saint Helena">Saint Helena</option>
                              <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                              <option value="Saint Lucia">Saint Lucia</option>
                              <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                              <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                              <option value="Samoa">Samoa</option>
                              <option value="San Marino">San Marino</option>
                              <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                              <option value="Saudi Arabia">Saudi Arabia</option>
                              <option value="Senegal">Senegal</option>
                              <option value="Serbia">Serbia</option>
                              <option value="Seychelles">Seychelles</option>
                              <option value="Sierra Leone">Sierra Leone</option>
                              <option value="Singapore">Singapore</option>
                              <option value="Slovakia">Slovakia</option>
                              <option value="Slovenia">Slovenia</option>
                              <option value="Solomon Islands">Solomon Islands</option>
                              <option value="Somalia">Somalia</option>
                              <option value="South Africa">South Africa</option>
                              <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                              <option value="Spain">Spain</option>
                              <option value="Sri Lanka">Sri Lanka</option>
                              <option value="Sudan">Sudan</option>
                              <option value="Suriname">Suriname</option>
                              <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                              <option value="Swaziland">Swaziland</option>
                              <option value="Sweden">Sweden</option>
                              <option value="Switzerland">Switzerland</option>
                              <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                              <option value="Taiwan">Taiwan</option>
                              <option value="Tajikistan">Tajikistan</option>
                              <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                              <option value="Thailand">Thailand</option>
                              <option value="Timor-leste">Timor-leste</option>
                              <option value="Togo">Togo</option>
                              <option value="Tokelau">Tokelau</option>
                              <option value="Tonga">Tonga</option>
                              <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                              <option value="Tunisia">Tunisia</option>
                              <option value="Turkey">Turkey</option>
                              <option value="Turkmenistan">Turkmenistan</option>
                              <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                              <option value="Tuvalu">Tuvalu</option>
                              <option value="Uganda">Uganda</option>
                              <option value="Ukraine">Ukraine</option>
                              <option value="United Arab Emirates">United Arab Emirates</option>
                              <option value="United Kingdom">United Kingdom</option>
                              <option value="United States">United States</option>
                              <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                              <option value="Uruguay">Uruguay</option>
                              <option value="Uzbekistan">Uzbekistan</option>
                              <option value="Vanuatu">Vanuatu</option>
                              <option value="Venezuela">Venezuela</option>
                              <option value="Viet Nam">Viet Nam</option>
                              <option value="Virgin Islands, British">Virgin Islands, British</option>
                              <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                              <option value="Wallis and Futuna">Wallis and Futuna</option>
                              <option value="Western Sahara">Western Sahara</option>
                              <option value="Yemen">Yemen</option>
                              <option value="Zambia">Zambia</option>
                              <option value="Zimbabwe">Zimbabwe</option>
                            </datalist>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <label for="civil" class="form-label">Civil Status</label>
                            <select class="form-select" aria-label="Default select example" name="civil" id="civil" required>
                              <option value="" hidden>Select</option>
                              <option value="Single">Single</option>
                              <option value="Married">Married</option>
                              <option value="Divorced">Divorced</option>
                              <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="religion" class="form-label">Religion</label>
                            <input class="form-control" list="lists3" id="religion" name="religion" required>
                            <datalist id="lists3">
                              <option value="" hidden>Select</option>
                              <option value="Roman Catholic">Roman Catholic</option>
                              <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                              <option value="Jehova\'s Witnesses">Jehova\'s Witnesses</option>
                              <option value="Born Again">Born Again</option>
                            </datalist>
                          </div>
                        </div>';
                      if($checkmodal3 == 1){
                        echo '<input type="hidden" name="email" id="email" value="'.$email1.'">
                              <input type="hidden" id="contact" name="contact" value="'.$contact1.'">
                              <input type="hidden"name="occupation" id="occupation" value="None">
                              <input type="hidden"name="nature" id="occupation" value="None">';
                      }
                      else{
                        echo '<div class="row">
                                <div class="col-md-12">
                                  <label for="email" class="form-label">Email Address</label>
                                  <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12">
                                  <label for="contact" class="form-label">Contact #(e.g +63 9*********)</label>
                                    <div class="input-group">
                                      <span class="input-group-text">+63</span>
                                      <input type="tel" class="form-control" id="contact" name="contact" pattern="[9]{1}[0-9]{9}" required>
                                    </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12">
                                  <label for="occupation" class="form-label">Occupation</label>
                                  <input type="text" class="form-control" name="occupation" id="occupation" placeholder="Occupation..." required>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12">
                                  <label for="nature" class="form-label">Nature of work</label>
                                  <select class="form-select" aria-label="Default select example" name="nature" id="nature" required>
                                    <option value="" hidden>Select</option>
                                    <option value="Architecture and Engineering">Architecture and Engineering</option>
                                    <option value="Arts, Culture and Entertainment">Arts, Culture and Entertainment</option>
                                    <option value="Business, Management and Administration">Business, Management and Administration</option>
                                    <option value="Communications">Communications</option>
                                    <option value="Community and Social Services">Community and Social Services</option>
                                    <option value="Education">Education</option>
                                    <option value="Science and Technology">Science and Technology</option>
                                    <option value="Installation, Repair and Maintenance">Installation, Repair and Maintenance</option>
                                    <option value="Farming, Fishing and Forestry">Farming, Fishing and Forestry</option>
                                    <option value="Government">Government</option>
                                    <option value="Health and Medicine">Health and Medicine</option>
                                    <option value="Law and Public Policy">Law and Public Policy</option>
                                    <option value="Sales">Sales</option>
                                    <option value="None">None</option>
                                  </select>
                                </div>
                              </div>';
                      }
                }
                if($checkmodal1 == 1){
                  if($checkmodal4 == 1){
                    if($family_role == "Son"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Son" selected>Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($family_role == "Daugther"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Son">Son</option>
                                  <option value="Daugther" selected>Daugther</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    else{
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Son">Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Relative" selected>Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                  }
                  else{
                    if($family_role == "Father"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father" selected>Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Grandfather">Grandfather</option>
                                  <option value="Grandmother">Grandmother</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($family_role == "Mother"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father">Father</option>
                                  <option value="Mother" selected">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Grandfather">Grandfather</option>
                                  <option value="Grandmother">Grandmother</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($family_role == "Son"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father">Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son" selected>Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Grandfather">Grandfather</option>
                                  <option value="Grandmother">Grandmother</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($family_role == "Daugther"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father">Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daugther" selected>Daugther</option>
                                  <option value="Grandfather">Grandfather</option>
                                  <option value="Grandmother">Grandmother</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($family_role == "Grandfather"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father">Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Grandfather" selected>Grandfather</option>
                                  <option value="Grandmother">Grandmother</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    elseif($family_role == "Grandmother"){
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father">Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Grandfather">Grandfather</option>
                                  <option value="Grandmother" selected>Grandmother</option>
                                  <option value="Relative">Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                    else{
                      echo '<div class="row">
                              <div class="col-md-12">
                                <label for="family_role" class="form-label">Family Role</label>
                                <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                  <option value="Father">Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daugther">Daugther</option>
                                  <option value="Grandfather">Grandfather</option>
                                  <option value="Grandmother">Grandmother</option>
                                  <option value="Relative" selected>Relative</option>
                                </select>
                              </div>
                            </div>';
                    }
                  }
                  
                }
                else{
                  if($checkmodal3 == 1){
                    echo '<div class="row">
                            <div class="col-md-12">
                              <label for="family_role" class="form-label">Family Role</label>
                              <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                <option value="" hidden>Select</option>
                                <option value="Son">Son</option>
                                <option value="Daugther">Daugther</option>
                                <option value="Relative">Relative</option>
                              </select>
                            </div>
                          </div>';
                  }
                  else{
                    echo '<div class="row">
                            <div class="col-md-12">
                              <label for="family_role" class="form-label">Family Role</label>
                              <select class="form-select" aria-label="Default select example" name="family_role" id="family_role" required>
                                <option value="" hidden>Select</option>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Son">Son</option>
                                <option value="Daugther">Daugther</option>
                                <option value="Grandfather">Grandfather</option>
                                <option value="Grandmother">Grandmother</option>
                                <option value="Relative">Relative</option>
                              </select>
                            </div>
                          </div>';
                  }
                  
                }
              ?>
              <div class="row">
                <div class="col-md-12">
                  <?php
                  if($checkmodal3 == 1){
                    echo '<label class="form-label" for="image">Birth Certificate Photo</label>';
                  }
                  else{
                    echo '<label class="form-label" for="image">Verification ID</label>';
                  }
                  ?>
                  <input type="file" class="form-control" name="image" id = "image" accept=".jpg, .jpeg, .png" required>
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="indiadd">Add</button>
            <a href="register.php"><button type="button" class="btn btn-secondary" name="confirmresident">Cancel</button></a>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- adding individual pop-up modal end -->

    <!-- confirmation of adding individual pop-up modal -->
    <div class="modal fade" id="confaddindividual" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title">Househead Information</h5>
          </div>
          <div class="modal-body">
          <form action="register.php" method="post">
            <div class="row">
              <div class="col-md-12">
                <label for="firstname" class="form-label">Firstname</label>
                <p id="firstname" class="text-start fs-3"><?php echo $firstname;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="middlename" class="form-label">Middlename</label>
                <p id="middlename" class="text-start fs-3"><?php echo $middlename;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="lastname" class="form-label">Lastname</label>
                <p id="lastname" class="text-start fs-3"><?php echo $lastname;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <label for="gender" class="form-label">Gender</label>
                <p id="gender" class="text-start fs-3"><?php echo $gender;?></p>
              </div>
              <div class="col-md-3">
                <label for="age" class="form-label">Age</label>
                <p id="age" class="text-start fs-3"><?php echo $age;?></p>
              </div>
              <div class="col-md-5">
                <label for="birthdate" class="form-label">Birthdate</label>
                <p id="birthdate" class="text-start fs-3"><?php echo $birthdate;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="birthplace" class="form-label">Birthplace</label>
                <p id="birthplace" class="text-start fs-3"><?php echo $birthplace;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="citizenship" class="form-label">Citizenship</label>
                  <p id="citizenship" class="text-start fs-3"><?php echo $citizenship;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <label for="civil" class="form-label">Civil Status</label>
                <p id="civil" class="text-start fs-3"><?php echo $civil;?></p>
              </div>
              <div class="col-md-8">
                <label for="religion" class="form-label">Religion</label>
                <p id="religion" class="text-start fs-3"><?php echo $religion;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="contact" class="form-label">Contact #</label>
                <p id="contact" class="text-start fs-3">+63 <?php echo $contact;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="typeemail" class="form-label">Email Address</label>
                <p id="email" class="text-start fs-3"><?php echo $email;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="occupation" class="form-label">Occupation</label>
                <p id="occupation" class="text-start fs-3"><?php echo $occupation;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="nature" class="form-label">Nature of work</label>
                <p id="nature" class="text-start fs-3"><?php echo $nature;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label for="old" class="form-label">Old House #</label>
                <p id="old" class="text-start fs-3"><?php echo $old;?></p>
              </div>
              <div class="col-md-6">
                <label for="new" class="form-label">New House #</label>
                <p id="new" class="text-start fs-3"><?php echo $new;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="street" class="form-label">Street</label>
                <p id="street" class="text-start fs-3"><?php echo $street;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                  <label for="village" class="form-label">Village/Subdivision</label>
                  <p id="village" class="text-start fs-3"><?php echo $village;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label for="housetype" class="form-label">Household Type</label>
                <p id="housetype" class="text-start fs-3"><?php echo $housetype;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label for="family_position" class="form-label">Family Position</label>
                <p id="family_position" class="text-start fs-3"><?php echo $family_position;?></p>
              </div>
              <div class="col-md-6">
                <label for="family_role" class="form-label">Family Role</label>
                <p id="family_role" class="text-start fs-3"><?php echo $family_role;?></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <?php
                  if($checkmodal3 == 1){
                    echo '<label class="form-label" for="image">Birth Certificate Photo</label>';
                  }
                  else{
                    echo '<label class="form-label" for="image">Verification ID</label>';
                  }
                  ?>
                <img src="images/<?=$newImageName?>" class="card-img-top image-fluid" id="image" alt="...">
              </div>
            </div>
            <input type="hidden" name="resident_code" value="<?=$resident_code?>">
            <input type="hidden" name="family_position" value="<?= $family_position?>">
            <input type="hidden" name="firstname" value="<?= $firstname?>">
            <input type="hidden" name="middlename" value="<?= $middlename?>">
            <input type="hidden" name="lastname" value="<?= $lastname?>">
            <input type="hidden" name="gender" value="<?= $gender?>">
            <input type="hidden" name="birthdate" value="<?= $birthdate?>">
            <input type="hidden" name="birthplace" value="<?= $birthplace?>">
            <input type="hidden" name="citizenship" value="<?= $citizenship?>">
            <input type="hidden" name="religion" value="<?= $religion?>">
            <input type="hidden" name="contact" value="<?= $contact?>">
            <input type="hidden" name="email" value="<?= $email?>">
            <input type="hidden" name="occupation" value="<?= $occupation?>">
            <input type="hidden" name="nature" value="<?= $nature?>">
            <input type="hidden" name="old" value="<?= $old?>">
            <input type="hidden" name="new" value="<?= $new?>">
            <input type="hidden" name="street" value="<?= $street?>">
            <input type="hidden" name="village" value="<?= $village?>">
            <input type="hidden" name="civil" value="<?= $civil?>">
            <input type="hidden" name="housetype" value="<?= $housetype?>">
            <input type="hidden" name="family_role" value="<?= $family_role?>">
            <input type="hidden" name="newImageName" value="<?= $newImageName?>">
            <input type="hidden" name="birthdateguar" value="<?= $birthdateguar?>">
            <input type="hidden" name="checkmodal3" value="<?= $checkmodal3?>">
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="confmemadding">Confirm</button>
            <button type="submit" class="btn btn-secondary" name="reloadaddhead">Back</button>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- confirmation of adding individual pop-up modal end -->

    <!-- adding confirmation pop-up modal -->
    <div class="modal fade" id="addconfmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header p-3 mb-2 bg-secondary text-white">
            <h5 class="modal-title">Adding Resident</h5>
          </div>
          <div class="modal-body">
            <p class="text-center fs-4">Your request is now pending.</p>
          </div>
          <form action="login.php" method="post">
          <div class="modal-footer">
            <button type="submit" class="btn btn-secondary">OK</button>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- adding confirmation pop-up end modal -->

    <?php
    if($showModal == 0) {
      // CALL MODAL HERE
      echo '<script type="text/javascript">
        $(document).ready(function(){
          $("#registermodal").modal("show");
        });
      </script>';
    }
    if($showModal == 1) {
      // CALL MODAL HERE
      echo '<script type="text/javascript">
        $(document).ready(function(){
          $("#censusmodal").modal("show");
        });
      </script>';
    }
    elseif($showModal == 2) {
      // CALL MODAL HERE
      echo '<script type="text/javascript">
        $(document).ready(function(){
          $("#addindividual").modal("show");
        });
      </script>';
    }
    elseif($showModal == 3) {
      // CALL MODAL HERE
      echo '<script type="text/javascript">
        $(document).ready(function(){
          $("#confaddindividual").modal("show");
        });
      </script>';
    }
    elseif($showModal == 4) {
      // CALL MODAL HERE
      echo '<script type="text/javascript">
        $(document).ready(function(){
          $("#addconfmodal").modal("show");
        });
      </script>';
    }
    elseif($showModal == 5) {
      // CALL MODAL HERE
      echo '<script type="text/javascript">
        $(document).ready(function(){
          $("#censusmodalminor").modal("show");
        });
      </script>';
    }
    ?>
  </body>
</html>
