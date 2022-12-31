<?php
require_once './vendor/autoload.php';
//dd(config('app_name'));
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>
    <?php
    vite()
    ?>

</head>
<body>
    <div class="container">
        <div class="row card">
            <div class="col-12 card-body">
                <h1 class='text-center'>Welcome To <?php echo config('app_name') ?></h1>

                <div class="d-flex justify-content-center preview-upload">
                  <!-- The selected photo will be displayed here. This is always very important-->
                </div>

                <form enctype="multipart/form-data" action="<?php echo config('app_url')."/app/controller/submit.php"; ?>" class="form" method="post">
                    <div class="form-group mb-3">
                        <label>Upload an image</label>
                        <input type="file" class="form-control image" name="image" data-preview="preview-upload" accept="image/*"/>
                    </div>
                    
                    <div class='row'>
                        <div class="col-6">
                            <label>First Name</label>
                            <input type="text" class="form-control borderless-input" name="first_name" />
                        </div>
                        <div class="col-6">
                            <label>Last Name</label>
                            <input type="text" class="form-control borderless-input" name="last_name" />
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>Birth Date</label>
                        <input type="date" class="form-control borderless-input" name="dob" />
                    </div>

                    <div class="form-group mt-3">
                        <label>Address</label>
                        <input type="text" class="form-control borderless-input" name="address" />
                    </div>

                    <div class="form-group mt-3">
                        <label>Credit Card</label>
                        <input type="number" class="form-control borderless-input" name="cc" />
                    </div>

                  <div class="form-group mt-3">
                  <input class="btn btn-primary btn-lg w-100" type="submit" value="Submit" />
                  </div>
              </form>

          </div>
      </div>
  </div>
  </body>
</html>
