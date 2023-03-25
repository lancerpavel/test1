<?php
// added this
namespace backend\controllers;

include(__DIR__.'/../../common/components/iloveimg/init.php');
use Iloveimg\Iloveimg;
use common\models\Car;
use common\models\Options;
use common\models\CarImage;
use backend\models\UploadForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

/**
 * CarController implements the CRUD actions for Car model.
 */
class CarController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {            
        if (in_array($action->id, ['upload-image', 'delete-image'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all Car models.
     *
     * @return string
     */

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Car::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Car model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		//return $this->goHome();
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Car model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Car();
        $model->save(); 

        $this->redirect(['update', 'id' => $model->id]);

        // $upload = new UploadForm();
        // $option = Options::findOne(['option_name' => 'usd_course']);
        // $usd_course = $option->option_value;

        // if ($this->request->isPost) {
        //     if ($model->load($this->request->post()) && $model->save()) {

        //         $upload->image = UploadedFile::getInstances($upload, 'image');

        //         if(!empty($upload->image) && $upload->validate()) {
        //             foreach ($upload->image as $file) {
        //                 $filename = sha1_file($file->tempName) . '.' . $file->extension;
        //                 $path = str_replace('/admin', '', \Yii::getAlias('@webroot')) . '/uploads/' . $filename;
        //                 $file->saveAs($path);
        //                 $image = new CarImage();

        //                 $image->car_id = $model->id;
        //                 $image->filename = $filename;
        //                 $image->save();
        //             }
        //         }

        //         return $this->redirect(['view', 'id' => $model->id]);
        //     }
        // } else {
        //     $model->loadDefaultValues();
        // }

        // return $this->render('create', [
        //     'model' => $model,
        //     'upload' => $upload,
        //     'usd_course' => $usd_course
        // ]);
    }

    /**
     * Updates an existing Car model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $option = Options::findOne(['option_name' => 'usd_course']);
        $usd_course = $option->option_value;
        $upload = new UploadForm();
        $images = CarImage::find()->where(['car_id' => $model->id])->all();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // $upload->image = UploadedFile::getInstances($upload, 'image');

            // if(!empty($upload->image) && $upload->validate()) {

            //     $images_to_delete = CarImage::findAll(['car_id' => $model->id]);

            //     foreach ($images_to_delete as $deleted) {
            //         $deleted->delete();
            //     }
                
            //     foreach ($upload->image as $file) {
            //         $filename = sha1_file($file->tempName) . '.' . $file->extension;
            //         $path = str_replace('/admin', '', \Yii::getAlias('@webroot')) . '/uploads/' . $filename;
            //         $file->saveAs($path);
            //         $image = new CarImage();

            //         $image->car_id = $model->id;
            //         $image->filename = $filename;
            //         $image->save();
            //     }
            // }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'upload' => $upload,
            'images' => $images,
            'usd_course' => $usd_course,
        ]);
    }

    /**
     * Deletes an existing Car model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();



        return $this->redirect(['index']);
    }

    public function actionUploadImage($id) {
        
        $upload = new UploadForm();

        $upload->image = UploadedFile::getInstances($upload, 'image');

        if(!empty($upload->image) && $upload->validate()) {
            $file = $upload->image[0];
            
            $pathinfo = pathinfo($file->name);
            
            $filepath = str_replace('/admin', '', \Yii::getAlias('@webroot')) . '/uploads/' . $file->name;
            $filename = $file->name;
            $counter = 1;

            while(file_exists($filepath)) {
                $filename = $pathinfo['filename'].'_'.$counter.'.'.$pathinfo['extension'];
                $filepath = str_replace('/admin', '', \Yii::getAlias('@webroot')) . '/uploads/' . $filename;
                $counter++;
            }

            $file->saveAs($filepath);
            $image = new CarImage();

            $image->car_id = $id;
            $image->filename = $filename;
            $image->save();

            try {
                $download_path = str_replace('/admin', '', \Yii::getAlias('@webroot')) . '/uploads/';

                $iloveimg = new Iloveimg('project_public_1467f0dad8aebe6b67110802708d1a9a_cbO2a8a4334e6bc8cc44a18ea1b3d56f8353b','secret_key_838a431e68108298451c49d7b761091f_JidLp6fd3effad00fb76ce8b8d9d7efc27e31');
                $myTask = $iloveimg->newTask('compress');
                $file1 = $myTask->addFile($filepath);
                $myTask->execute();
                $myTask->download($download_path);

                $image->is_compressed = 1;
                $image->save();
            } catch (Exception $e) {  
            
            }

            $result = [
                "error" => null,
                "errorkeys" => [],
                "filenames" => [$image->filename],
                "initialPreview" => ["<img src='/uploads/".$image->filename."' class='file-preview-image'>"],
                "initialPreviewConfig" => [["key" => $image->id, "url" => "/admin/car/delete-image?id=".$image->id, "caption" => $image->filename]],
                "append" => true 
            ];
        } else {
            $result = [
                "error" => 'file is empty',
                "errorkeys" => [], 
            ];            
        }

        echo json_encode($result);
    }

    public function actionDeleteImage($id) {
        $img = CarImage::findOne($id);
        $img->delete();
        
        echo 'true';
    }

    /**
     * Finds the Car model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Car the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Car::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
