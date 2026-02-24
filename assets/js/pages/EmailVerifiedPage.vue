<template>
  <div class="container py-5">
    <div class="row justify-content-center align-items-center">

      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h2 class="card-title mb-4 text-center">Email Verified!</h2>
            <p class="text-center mb-4">Your email has been successfully verified. You can now log in to your account.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 d-none d-md-block text-end">
        <img :src="require('@images/auth/verified.png')" alt="Login" class="img-fluid" />
      </div>

    </div>
  </div>
</template>