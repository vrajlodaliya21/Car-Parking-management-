<?php
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: Index.php');
    exit();
}
include 'Connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: Index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>User Dashboard</title>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <link rel='stylesheet' href='./Styles.css'>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .fade-out {
            opacity: 1;
            transition: opacity 1s ease-out;
        }

        .fade-out.hidden {
            opacity: 0;
            display: none;
        }

        .bg-image {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('./Images/Parking.jpg');
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 100vh;
            position: relative;
        }

        .home {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-top: 250px;
            padding-right: 100px;
            color: white;
            text-align: right;
        }

        .home-text {
            background: rgba(0, 0, 0, 0.5);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 600px;
        }

        .home-text div {
            font-size: 38px;
            line-height: 1.3;
            font-weight: 700;
        }

        .home-text div b #span {
            color: #00d2ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 42px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn1 {
            background-color: #0385a6;
            width: 130px;
            height: 42px;
            font-size: 18px;
        }

        .heading-1 {
            font-size: 40px;
        }

        .special-text {
            font-size: 25px;
        }

        .smart > h4 {
            font-size: 29px;
        }

        .smart > p {
            font-size: 20px;
        }

        .container h1 #span {
            color: #0385a6;
            font-family: 'MV Boli', sans-serif;
            font-size: 50px;
        }

        @media only screen and (max-width:400px) {
            .home {
                padding-top: 240px;
                padding-right: 90px;
            }

            .home-text div {
                font-size: 30px;
            }

            .home-text div b #span {
                font-size: 35px;
            }
        }
    </style>
</head>

<body>
    <div>
        <?php
        require_once './Includes/Unavbar.php';
        ?>

        <section>
            <div class='bg-image'>
                <div class='container-fluid'>
                    <div class='home'>
                        <div class='home-text'>
                            <div class='text-capitalize'>
                                <b><span id='span'>Park Your Worries, </span><br /> not just your car. <br />
                                    Finding The Perfect <br /> spot made easy </b>
                            </div>
                            <a href='UserBookSlot.php'>
                                <button class='btn btn1 px-3 mt-3'><b>Book Now..</b></button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <section>
            <div class='container col-11'>
                <div class='row mb-5'>
                    <div class='col-lg-6 col-md-6 col-12 mt-3'>
                        <img src='./Images/new1.jpg' class='card-img-top' alt='Card Image'>
                    </div>

                    <div class='col-lg-6 col-md-6 col-12 mt-5 pl-3 smart'>
                        <h4 class="py-3">An End-to-End Parking Management System</h4>
                        <p class="py-2">  &#9679; Our Smart Parking System is a unique parking management solution. Suitable for all types of parking areas, it digitizes end-to end parking processes including multi-tenant, multi-level parking. 
                        </p>
                        
                        <p> &#9679; It is integrated with visitor management system, FASTag, and access control hardware. Extremely useful for shared parking spaces, the solution automates day-to-day processes such as auto-identifying appropriate parking slots - be it reserved or pay-and-park, auto-generating parking tickets, levying penalties, and many more.</p>
                    </div>
                </div>
            </div>
        </section>





        <section>
            <?php require_once './Includes/footer.php' ?>
        </section>

    </div>
</body>

</html>
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>