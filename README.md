
PRE-REQUISITE:

   Install the dependent package to require the package into Modules folder,

   composer require joshbrw/laravel-module-installer


USAGE STEPS:

   composer require techlifyinc/filemanager-module
 
   Import the FileManger model required to the controller. (use Modules\FileManager\Entities\FileManager;)

API:

   1) FileManager::upload(disk_name, sub_path);

      Returns:
           [
             'url' => 'Absolute URL',
             'file' => Relative URL,
             'file_size' => Uploaded file size
           ]; 

   2) FileManager::delete(relative_url);

      Returns: True or False (Boolean)
    





