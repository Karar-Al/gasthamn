<?php
/*

  @package Hamn
*/

get_header(); ?>

<body>
  <?php wp_body_open(); ?>

  <?php if (is_front_page()) { ?>
    <div class="bg-light">
      <div class="container col-xxl-8 px-4 py-5">
        <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
          <div class="col-10 col-sm-8 col-lg-6">
            <img src="<?php echo home_url(); ?>/wp-content/uploads/2022/03/Hotelroom-honolulu-harbor.jpg" class="d-none d-lg-block mx-lg-auto img-fluid rounded shadow-lg border border-dark" alt="Bild på vår hamn." loading="lazy">
          </div>
          <div class="col-lg-6">
            <h1 class="display-5 fw-bold lh-1 mb-3">Välkommen till Gästhamnsajten!</h1>
            <p class="lead"></p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
              <a type="button" href="#" class="btn btn-primary btn-lg px-4 me-md-2">Boka plats</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>

  <main class="container mt-5">
    <section class="row g-5">
      <article class="col-md-8">
        <?php
          if (have_posts()) {
            while (have_posts()) : the_post();
        ?>

        <?php if (!is_front_page()) { ?>
          <h2><?php the_title(); ?></h2>
          <?php if (get_post_type() === 'post') { ?>
            <h4>
              <span>Posted: <?php the_time("F jS, Y"); ?></span>
            </h4>
          <?php }?>
        <?php } ?>

        <?php
            the_content("[more...]");
            endwhile;
          } else {
            echo "<p>"
              . _e('Sorry, no posts matched your criteria.')
              . "</p>";
          }
        ?>
      </article>
      <aside class="col-md-4">
        <div class="position-sticky" style="top:2rem;">
          <h2>Se våra inlägg</h2>
          <?php
            wp_nav_menu( array( 'menu' => 'posts', 'items_wrap' => '%3$s' ) );
          ?>
        </div>
      </aside>
    </section>

  </main>
  <?php get_footer(); ?>

</body>
</html>