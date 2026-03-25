<!DOCTYPE html>
<html lang="en">
<head>

     <title>smasa-academics</title>

     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=Edge">
     <meta name="description" content="">
     <meta name="keywords" content="">
     <meta name="author" content="">
     <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

     <link rel="stylesheet" href="{{asset('/asset/css/bootstrap.min.css')}}">
     <link rel="stylesheet" href="{{asset('/asset/css/font-awesome.min.css')}}">
     <link rel="stylesheet" href="{{asset('/asset/css/owl.carousel.css')}}">
     <link rel="stylesheet" href="{{asset('/asset/css/owl.theme.default.min.css')}}">

     <!-- MAIN CSS -->
     <link rel="stylesheet" href="{{asset('/asset/css/templatemo-style.css')}}">
     
     <!-- Additional custom styles for Back to Top button -->
     <style>
         /* Back to Top Button Styles */
         .back-to-top {
             position: fixed;
             bottom: 30px;
             right: 30px;
             width: 50px;
             height: 50px;
             background-color: #3F51B5;  /* Matching the site's primary color tone */
             color: #fff;
             border-radius: 50%;
             text-align: center;
             line-height: 50px;
             font-size: 22px;
             cursor: pointer;
             z-index: 9999;
             opacity: 0;
             visibility: hidden;
             transition: all 0.3s ease-in-out;
             box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
             border: 1px solid rgba(255,255,255,0.2);
         }
         
         .back-to-top.show {
             opacity: 1;
             visibility: visible;
         }
         
         .back-to-top:hover {
             background-color: #2c4a3b;
             transform: translateY(-3px);
             box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
         }
         
         .back-to-top:active {
             transform: translateY(0);
         }
         
         /* Smooth scroll behavior for the whole page */
         html {
             scroll-behavior: smooth;
         }
         
         /* Small screen adjustment */
         @media (max-width: 768px) {
             .back-to-top {
                 width: 42px;
                 height: 42px;
                 line-height: 42px;
                 font-size: 18px;
                 bottom: 20px;
                 right: 20px;
             }
         }
     </style>

</head>
<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">

     <!-- PRE LOADER -->
     <!-- <section class="preloader">
          <div class="spinner">

               <span class="spinner-rotate"></span>
               
          </div>
     </section> -->


     <!-- MENU -->
     <section class="navbar custom-navbar navbar-fixed-top" role="navigation">
          <div class="container">

               <div class="navbar-header">
                    <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                         <span class="icon icon-bar"></span>
                         <span class="icon icon-bar"></span>
                         <span class="icon icon-bar"></span>
                    </button>

                    <!-- lOGO TEXT HERE -->
                    <a href="#" class="navbar-brand">SMASA</a>
               </div>

               <!-- MENU LINKS -->
               <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-nav-first">
                         <li><a href="#top" class="smoothScroll">Home</a></li>
                         <li><a href="#about" class="smoothScroll">About</a></li>
                        
                         <li><a href="#courses" class="smoothScroll">Schools</a></li>
                         <li><a href="#testimonial" class="smoothScroll">Reviews</a></li>
                         <li><a href="#contact" class="smoothScroll">Contact</a></li>
                          <li><a href="{{route('users.login')}}" class="smoothScroll">Login</a></li>
                    </ul>

                    <ul class="nav navbar-nav navbar-right">
                         <li><a href="#"><i class="fa fa-phone"></i> +256 702 082 209 / +256 701 098 373</a></li>
                    </ul>
               </div>

          </div>
     </section>


     <!-- HOME -->
     <section id="home">
          <div class="row">

                    <div class="owl-carousel owl-theme home-slider">
                         <div class="item item-first">
                              <div class="caption">
                                   <div class="container">
                                        <div class="col-md-6 col-sm-12">
                                             <h1>Don't let outdated software slow down your school programs</h1>
                                             <h3>Techsate presents a comprehensive, cloud-based solution for Theology Schools, primary, and curriculum programs.</h3>
                                             <a href="{{route('users.login')}}" class="section-btn btn btn-default smoothScroll">Login</a>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="item item-second">
                              <div class="caption">
                                   <div class="container">
                                        <div class="col-md-6 col-sm-12">
                                             <h1>Empowering Islamic Theology Institutions with Seamless Technology.</h1>
                                             <h3>Smart Technologies introduces a comprehensive ecosystem designed specifically for the  Islamic Theology institutes and non-islamic institutes.</h3>
                                             <a href="{{route('users.login')}}"class="section-btn btn btn-default smoothScroll">Login</a>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="item item-third">
                              <div class="caption">
                                   <div class="container">
                                        <div class="col-md-6 col-sm-12">
                                             <h1>Your Entire Institute in Your Pocket.</h1>
                                             <h3>Manage lessons, grades, and student data from anywhere in the world.The First AI-Powered Mobile Solution for Islamic Theology Schools.</h3>
                                             <a href="{{route('users.login')}}" class="section-btn btn btn-default smoothScroll">Login</a>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>
          </div>
     </section>


     <!-- FEATURE -->
     <section id="feature">
          <div class="container">
               <div class="row">

                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <span>01</span>
                              <h3>Student Portal</h3>
                              <p>A central hub for course materials , assignment submissions, and financial aid tracking.</p>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <span>02</span>
                              <h3>Faculty Tools</h3>
                              <p>Simplify the management of adjunct professors and guest lecturers.</p>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <span>03</span>
                              <h3>Accreditation Ready</h3>
                              <p>Generate students reports, performance analytics and communicates with iteb system</p>
                         </div>
                    </div>

               </div>
          </div>
     </section>


     <!-- ABOUT -->
     <section id="about">
          <div class="container">
               <div class="row">

                    <div class="col-md-6 col-sm-12">
                         <div class="about-info">
                              <h2>Join the movement of schools using technology to spread the Word more effectively.</h2>

                              <figure>
                                   <span><i class="fa fa-users"></i></span>
                                   <figcaption>
                                        <h3>Seamless Examination Board  Integration</h3>
                                        <p>Say goodbye to manual data entry. Our system directly integrates with the IDAAD and Thanawi Examination portals,
                                              allowing for instant registration and verification of candidate students.</p>
                                   </figcaption>
                              </figure>

                              <figure>
                                   <span><i class="fa fa-certificate"></i></span>
                                   <figcaption>
                                        <h3>general performance analysis </h3>
                                        <p>Identify trends and performance analytics across the entire academic term/year .</p>
                                   </figcaption>
                              </figure>

                              <figure>
                                   <span><i class="fa fa-bar-chart-o"></i></span>
                                   <figcaption>
                                        <h3>Arabic/English language switcher</h3>
                                        <p>ensuring that students, faculty, and administrators can work in the language they are most comfortable with</p>
                                   </figcaption>
                              </figure>
                         </div>
                    </div>

                    <div class="col-md-offset-1 col-md-4 col-sm-12">
    <div class="entry-form">
        <img src="{{asset('/asset/images/courses-image1.png')}}" alt="School management system features">
    </div>
</div>

               </div>
          </div>
     </section>


     

     <!-- Courses -->
     <section id="courses">
          <div class="container">
               <div class="row">

                    <div class="col-md-12 col-sm-12">
                         <div class="section-title">
                              <h2>Our Schools <small>Our school coverage</small></h2>
                         </div>

                         <div class="owl-carousel owl-theme owl-courses">
                              <div class="col-md-4 col-sm-4">
                                   <div class="item">
                                        <div class="courses-thumb">
                                             <div class="courses-top">
                                                  <div class="courses-image">
                                                       <img src="{{asset('/asset/images/courses-image1.png')}}" class="img-responsive" alt="">
                                                  </div>
                                                  
                                             </div>

                                             <div class="courses-detail">
                                                  <h3><a href="#">Primary Schools</a></h3>
                                                  <p></p>
                                             </div>

                                             <div class="courses-info">
                                                  <div class="courses-author">
                                                       <img src="{{asset('/asset/images/author-image1.png')}}" class="img-responsive" alt="">
                                                       <span>Charges per Student</span>
                                                  </div>
                                                  <div class="courses-price">
                                                       <a href="#"><span>Ugx.1000</span></a>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-md-4 col-sm-4">
                                   <div class="item">
                                        <div class="courses-thumb">
                                             <div class="courses-top">
                                                  <div class="courses-image">
                                                       <img src="{{asset('/asset/images/courses-image2.png')}}" class="img-responsive" alt="">
                                                  </div>
                                                  
                                             </div>

                                             <div class="courses-detail">
                                                  <h3><a href="#">Idaad Schools</a></h3>
                                                 
                                             </div>

                                             <div class="courses-info">
                                                  <div class="courses-author">
                                                       <img src="{{asset('/asset/images/author-image2.png')}}" class="img-responsive" alt="">
                                                       <span>Charges Per students</span>
                                                  </div>
                                                  <div class="courses-price">
                                                       <a href="#"><span>Ugx.1000</span></a>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-md-4 col-sm-4">
                                   <div class="item">
                                        <div class="courses-thumb">
                                             <div class="courses-top">
                                                  <div class="courses-image">
                                                       <img src="{{asset('/asset/images/courses-image3.png')}}" class="img-responsive" alt="">
                                                  </div>
                                                 
                                             </div>

                                             <div class="courses-detail">
                                                  <h3><a href="#">Thanawi Schools</a></h3>
                                                 
                                             </div>

                                             <div class="courses-info">
                                                  <div class="courses-author">
                                                       <img src="{{asset('/asset/images/author-image3.png')}}" class="img-responsive" alt="">
                                                       <span>Charges per student</span>
                                                  </div>
                                                  <div class="courses-price free">
                                                       <a href="#"><span>Ugx.1000</span></a>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              
               </div>
          </div>
     </section>


     <!-- TESTIMONIAL -->
     <section id="testimonial">
          <div class="container">
               <div class="row">

                    <div class="col-md-12 col-sm-12">
                         <div class="section-title">
                              <h2>Schools Reviews <small>from around the world</small></h2>
                         </div>

                         <div class="owl-carousel owl-theme owl-client">
                              <div class="col-md-4 col-sm-4">
                                   <div class="item">
                                        <div class="tst-image">
                                             <img src="{{asset('/asset/images/tst-image1.png')}}" class="img-responsive" alt="">
                                        </div>
                                        <div class="tst-author">
                                             <h4>Fatima Hassan</h4>
                                             <span>Principal</span>
                                        </div>
                                        <p>We switched to this system six months ago, and it has been a revelation for our administrative team. The interface is intuitive, making it incredibly easy for our teachers to take attendance and record grades. </p>
                                        <div class="tst-rating">
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-md-4 col-sm-4">
                                   <div class="item">
                                        <div class="tst-image">
                                             <img src="{{asset('/asset/images/tst-image2.png')}}" class="img-responsive" alt="">
                                        </div>
                                        <div class="tst-author">
                                             <h4>Ustadh Ridwan Malik</h4>
                                             <span>Head of Islamic Studies</span>
                                        </div>
                                        <p>As a school focusing on Islamic theology, we have very specific needs that standard software just doesn't meet. This system is different. The ability to track Hifdh (memorization) progress with detailed lesson plans  .</p>
                                        <div class="tst-rating">
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-md-4 col-sm-4">
                                   <div class="item">
                                        <div class="tst-image">
                                             <img src="{{asset('/asset/images/tst-image3.png')}}" class="img-responsive" alt="">
                                        </div>
                                        <div class="tst-author">
                                             <h4>Muhammad Al-Amin</h4>
                                             <span>Idaad Coordinator</span>
                                        </div>
                                        <p>Managing students transitioning into higher levels of Islamic learning requires a robust system, and this platform delivers.We particularly appreciate the gradebook features that allow us to weight different assessments—crucial for our  curriculum</p>
                                        <div class="tst-rating">
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                             <i class="fa fa-star"></i>
                                        </div>
                                   </div>
                              </div>

                              
                         

               </div>
          </div>
     </section> 


     <!-- CONTACT -->
     <section id="contact">
          <div class="container">
               <div class="row">

                    <div class="col-md-6 col-sm-12">
                         <form id="contact-form" role="form" action="" method="post">
                              <div class="section-title">
                                   <h2>Contact us <small>we love conversations. let us talk!</small></h2>
                              </div>

                              <div class="col-md-12 col-sm-12">
                                   <input type="text" class="form-control" placeholder="Enter full name" name="name" required="">
                    
                                   <input type="email" class="form-control" placeholder="Enter email address" name="email" required="">

                                   <textarea class="form-control" rows="6" placeholder="Tell us about your message" name="message" required=""></textarea>
                              </div>

                              <div class="col-md-4 col-sm-12">
                                   <input type="submit" class="form-control" name="send message" value="Send Message">
                              </div>

                         </form>
                    </div>

                    <div class="col-md-6 col-sm-12">
                         <div class="contact-image">
                              <img src="{{asset('/asset/images/contact-image.png')}}" class="img-responsive" alt="Smiling Two Girls">
                         </div>
                    </div>

               </div>
          </div>
     </section>       


     <!-- FOOTER -->
     <footer id="footer">
          <div class="container">
               <div class="row">

                    <div class="col-md-4 col-sm-6">
                         <div class="footer-info">
                              <div class="section-title">
                                   <h2>Headquarter</h2>
                              </div>
                              <address>
                                   <p>Bwaise-kampala,<br> Sky City Building</p>
                              </address>

                              <ul class="social-icon">
                                   <li><a href="#" class="fa fa-facebook-square" attr="facebook icon"></a></li>
                                   <li><a href="#" class="fa fa-twitter"></a></li>
                                   <li><a href="#" class="fa fa-instagram"></a></li>
                              </ul>

                              <div class="copyright-text"> 
                                   <p>Copyright &copy; 2026 Techsate</p>
                                   
                              </div>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                         <div class="footer-info">
                              <div class="section-title">
                                   <h2>Contact Info</h2>
                              </div>
                              <address>
                                   <p>+256 702 082 209, +256 701 098 373</p>
                                   <p><a href="mailto:techsate.com">info@techsate.com</a></p>
                              </address>

                              <div class="footer_menu">
                                   <h2>Quick Links</h2>
                                   <ul>
                                        <li><a href="#">Home</a></li>
                                        <li><a href="#">About</a></li>
                                        <li><a href="#">Our schools</a></li>
                                        <li><a href="#">Login</a></li>
                                   </ul>
                              </div>
                         </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                         <div class="footer-info newsletter-form">
                              <div class="section-title">
                                   <h2>Newsletter Signup</h2>
                              </div>
                              <div>
                                   <div class="form-group">
                                        <form action="#" method="get">
                                             <input type="email" class="form-control" placeholder="Enter your email" name="email" id="email" required="">
                                             <input type="submit" class="form-control" name="submit" id="form-submit" value="Send me">
                                        </form>
                                        <span><sup>*</sup> Please note - we do not spam your email.</span>
                                   </div>
                              </div>
                         </div>
                    </div>
                    
               </div>
          </div>
     </footer>


     <!-- BACK TO TOP BUTTON -->
     <div class="back-to-top" id="backToTopBtn" aria-label="Back to top" title="Back to top">
          <i class="fa fa-arrow-up"></i>
     </div>


     <!-- SCRIPTS -->
     <script src="/asset/js/jquery.js"></script>
     <script src="/asset/js/bootstrap.min.js"></script>
     <script src="/asset/js/owl.carousel.min.js"></script>
     <script src="/asset/js/smoothscroll.js"></script>
     <script src="/asset/js/custom.js"></script>
     
     <!-- Custom script for Back to Top functionality -->
     <script>
         (function() {
             // Get the button element
             var backToTopBtn = document.getElementById('backToTopBtn');
             
             // Function to check scroll position and show/hide button
             function toggleBackToTopButton() {
                 if (window.pageYOffset > 300) {  // Show after scrolling down 300px
                     backToTopBtn.classList.add('show');
                 } else {
                     backToTopBtn.classList.remove('show');
                 }
             }
             
             // Function to smoothly scroll to top
             function scrollToTop() {
                 // Smooth scroll behavior is already set in CSS for html, but we'll add fallback
                 window.scrollTo({
                     top: 0,
                     behavior: 'smooth'
                 });
                 
                 // Also handle any hash changes to ensure we're at top
                 if (window.location.hash) {
                     // Remove hash without triggering page jump
                     history.pushState("", document.title, window.location.pathname + window.location.search);
                 }
             }
             
             // Add scroll event listener
             window.addEventListener('scroll', toggleBackToTopButton);
             
             // Add click event listener to button
             if (backToTopBtn) {
                 backToTopBtn.addEventListener('click', scrollToTop);
             }
             
             // Initial check in case page loads with scroll position
             toggleBackToTopButton();
             
             // Optional: For mobile devices and better compatibility with existing smoothScroll
             // Also ensure that the button doesn't conflict with existing smoothScroll links
             var smoothScrollLinks = document.querySelectorAll('.smoothScroll');
             for (var i = 0; i < smoothScrollLinks.length; i++) {
                 smoothScrollLinks[i].addEventListener('click', function(e) {
                     // If any smoothScroll link points to #top, we can also hide/show accordingly
                     var target = this.getAttribute('href');
                     if (target === '#top') {
                         // The existing smoothScroll will handle it, we just ensure button hides later
                         setTimeout(function() {
                             if (window.pageYOffset < 100) {
                                 backToTopBtn.classList.remove('show');
                             }
                         }, 500);
                     }
                 });
             }
         })();
     </script>

</body>
</html>