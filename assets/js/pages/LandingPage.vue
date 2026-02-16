<template>
  <div>
    <!-- Hero Section -->
    <section class="py-3">
      <div class="container">
        <div class="row align-items-center gy-4">
          <div class="col-lg-6">
            <h1 class="display-5 fw-semibold mb-3">
              Turn Your Wishes<br />
              Into Reality
            </h1>
            <p class="lead text-muted mb-4">
              Manage your own wish list and discover gift inspiration based on other users' interests.
            </p>
            <div class="d-flex gap-3">
              <a href="/register" class="btn btn-accent btn-lg">Get Started</a>
              <a href="#how-it-works" class="btn btn-outline-primary btn-lg">Learn More</a>
            </div>
          </div>
          <div class="col-lg-6 text-center">
            <img :src="require('@images/hero-placeholder.png')" alt="Hero" class="img-fluid" />
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-5 section-soft">
      <div class="container">
        <div class="row text-center g-4">
          <div class="col-md-4">
            <div class="card card-hover h-100 p-4">
              <div class="mb-3 fs-1">
                <img :src="require('@images/hiw/hiw-wishlist.png')" alt="📝List" class="hw-img" />
              </div>
              <h5 class="fw-semibold">Create Wish List</h5>
              <p class="text-muted small">
                Add gifts, define categories, tags and price ranges.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card card-hover h-100 p-4">
              <div class="mb-3 fs-1">
                <img :src="require('@images/hiw/hiw-search.png')" alt="🔎Search" class="hw-img" />

              </div>
              <h5 class="fw-semibold">Find Gift Ideas</h5>
              <p class="text-muted small">
                Search for other users' wishes by categories and tags.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card card-hover h-100 p-4">
              <div class="mb-3 fs-1">
                <img :src="require('@images/hiw/hiw-check.png')" alt="🤝Match" class="hw-img" />
              </div>
              <h5 class="fw-semibold">Smart Matching</h5>
              <p class="text-muted small">
                Matching algorithm based on categories, tags and budget.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 section-soft text-center">
      <div class="container">
        <h3 class="fw-semibold mb-3">
          Ready to Make Your Wishes Come True?
        </h3>
        <p class="text-muted mb-4">
          Sign up and start building your smart wish list.
        </p>
        <div class="d-flex justify-content-center gap-3">
          <a href="/register" class="btn btn-accent btn-lg">Sign Up for Free</a>
          <a href="/login" class="btn btn-outline-primary btn-lg">Log In</a>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'LandingPage'
}
</script>

<style scoped>
 .hw-img {
   max-width: 200px;
 }

</style>
