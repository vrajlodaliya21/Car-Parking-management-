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
            background-image: url('./Images/bg3.jpg');
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
            padding-top: 300px;
            padding-right: 150px;
            color: Black;
            text-align: right;
        }

        .home-text div {
            font-size: 35px;
        }

        .home-text div b #span {
            color: #0385a6;
            font-family: 'MV Boli', sans-serif;
            font-size: 37px;
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
                                    <span id='span'>Finding The Perfect <br /> spot</span> made easy </b>
                            </div>
                            <a href='UserBookSlot.php'>
                                <button class='btn btn1 px-3 mt-3'><b>Book Now..</b></button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row">
            <h1 class='col-12 text-center text-capitalize py-5'>
                <b>
                    <div class="heading-1"> A <span> Smart Parking </span> Management System </div>
                </b>
                <p class="text-capitalize special-text mt-3">Our parking management system is an integrated smart
                    parking <br> system that automates end-to-end parking processes.</p>
            </h1>
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


        <section class='bg-content pb-5'>
            <div class='container'>
                <h1 class='text-center text-capitalize pt-5'>
                    <b><span id='span'> Products </span> Of Our Parking </b>
                </h1>
                <hr class='w-25 mx-auto pb-5 border-black border-dark' />
            </div>
            <div class='container col-11 text-center'>
                <div class='row mb-2'>
                    <div class='col-lg-4 col-md-4 col-12 mt-3'>
                        <div class='card'>
                            <img src='./Images/AI.jpg' class='card-img-top' alt='Card Image' height='223px'>
                            <div class='card-body'>
                                <h5 class='card-title mb-4 text-center h4'>Vision AI</h5>
                                <hr class='w-25 mx-auto border-black border-dark' />
                                <p class='card-text '>Vision-based technology with CCTV camera - monitors & counts people inflow outflow.</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-4 col-md-4 col-12 mt-3'>
                        <div class='card'>
                            <img src='./Images/FR.jpg' class='card-img-top' alt='Card Image' height='223px'>
                            <div class='card-body'>
                                <h5 class='card-title mb-4 text-center h4'>Facial Recognition System</h5>
                                <hr class='w-25 mx-auto border-black border-dark' />
                                <p class='card-text'>An attendance system using face ID with mask & temperature detection.</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-4 col-md-4 col-12 mt-3'>
                        <div class='card'>
                            <img src='./Images/VMS.jpg' class='card-img-top' alt='Card Image' height='223px'>
                            <div class='card-body'>
                                <h5 class='card-title mb-4 text-center h4'>Visitor Management System</h5>
                                <hr class='w-25 mx-auto border-black border-dark' />
                                <p class='card-text'>A touchless visitor management software to manage visitors across locations.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='row mb-2'>
                    <div class='col-lg-4 col-md-4 col-12 mt-3'>
                        <div class='card'>
                            <img src='./Images/Corp-Parking.jpg' class='card-img-top' alt='Card Image' height='223px'>
                            <div class='card-body'>
                                <h5 class='card-title mb-4 text-center h4'>Parking Management System</h5>
                                <hr class='w-25 mx-auto border-black border-dark' />
                                <p class='card-text '>A smart parking system that manages multi-tenant, multi-level parking.</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-4 col-md-4 col-12 mt-3'>
                        <div class='card'>
                            <img src='./Images/Guard.jpg' class='card-img-top' alt='Card Image' height='223px'>
                            <div class='card-body'>
                                <h5 class='card-title mb-4 text-center h4'>Guard Tour System</h5>
                                <hr class='w-25 mx-auto border-black border-dark' />
                                <p class='card-text'>A security guard tracking system that assigns & monitors duties, with real-time updates</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-4 col-md-4 col-12 mt-3'>
                        <div class='card'>
                            <img src='./Images/gps.jpg' class='card-img-top' alt='Card Image' height='223px'>
                            <div class='card-body'>
                                <h5 class='card-title mb-4 text-center h4'>24 x 7 Monitoring System</h5>
                                <hr class='w-25 mx-auto border-black border-dark' />
                                <p class='card-text'>24 x 7 monitoring system to monitor the parking area and the vehicles.</p>
                            </div>
                        </div>
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