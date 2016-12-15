<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Properties Controller
 *
 * @property \App\Model\Table\PropertiesTable $Properties
 */
class PropertiesController extends AppController
{

    public $paginate = [
        'limit' => 10,
    ];

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
		$this->Auth->allow(['search','home']);

    }



    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     * @author Touhidur Rahman
     */
    public function index()
    {
        // $this->paginate = [
        //     'contain' => ['Zips']
        // ];
        // $properties = $this->paginate($this->Properties);
        //
        // $this->set(compact('properties'));
        // $this->set('_serialize', ['properties']);
        $this->search();
    }



    /**
     * View method
     *
     * @param string|null $id Property id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @author Touhidur Rahman, Norman Lista
     */
    public function view($id = null)
    {
        //@author Norman Lista
        //feedback code
        $this->loadModel('Feedbacks');
        // , 'FavoriteProperties', 'Images'
        $property = $this->Properties->get($id, [
            'contain' => ['Zips', 'Users', 'Images']
        ]);
        if ($property->status == 0) {
            $this->Flash->error(__('Cannot show deactivated ads!'));
            return $this->redirect(['action' => 'myproperties']);
        }

       $feedbackSearch=$this->Feedbacks->find('all',[
           'conditions' => ['for_user_id =' => $property->user->id]]);

       if($feedbackSearch->isEmpty()){
           $feedback->rate=0;
       }else{$feedback =$feedbackSearch->first();}

       /**
        * @author Touhidur Rahman
        */
        // Retrieve landlord avg rating
        $avgRating = TableRegistry::get('AvgRatings')->find()->where(['user_id' => $property->user_id]);

        $this->set(compact('property', 'feedback', 'avgRating'));
        $this->set('_serialize', ['property']);
    }



    /**
     * Add method
     * Accepts a Zip ID otherwise redirects
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     * @author Touhidur Rahman,Ramanpreet Kaur
     */
    public function add($zipid=NULL)
    {
        // if there is no zip id redirect to select zip page
        // else, send the zip id to view file inside hidden field for onward retrieval
        if ($zipid == NULL) {
            return $this->redirect(['controller' => 'zips', 'action' => 'select']);
        }

        $property = $this->Properties->newEntity();

        if ($this->request->is('post')) {
            $property = $this->Properties->patchEntity($property, $this->request->data);
            // get reporter user's id from session
            $property->user_id = $this->Auth->user('id');
            // For now, let every property be approved automatically
            $property->status = 1;
            if ($this->Properties->save($property)) {
                $this->Flash->success(__('The ad is successfully created.'));

                return $this->redirect(['controller' => 'images', 'action' => 'add', $property->id]);
            } else {
                $this->Flash->error(__('The ad couldn\'t be created. Please, try again.'));
            }
        }

        // Set the layout.
        $this->viewBuilder()->layout('userdash');

        //@author Norman Lista
        //send user id for my profile button
        $id=$this->Auth->user('id');
        $this->set(compact('property', 'zipid'));
        $this->set('_serialize', ['property', 'zipid']);

    }




    /**
     * Edit method
     *
     * @param string|null $id Property id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     * @author Ramanpreet Kaur, Touhidur Rahman, Muneeb Noor
     */
    public function edit($id = null)
    {
        // only logged in user can edit his property
        $property = $this->Properties->get($id);

		//To ensure that only administrators are able to edit all the properties
		if($property->user_id != $this->Auth->user('id'))
		{
			$session = $this->request->session();
			   if($session->read('User.admin') != '1')
		return $this->redirect($this->referer());

		}

        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($property->user_id == $this->Auth->user('id')){
                $property = $this->Properties->patchEntity($property, $this->request->data);
                if ($this->Properties->save($property)) {
                    $this->Flash->success(__('The property ad has been modified.'));

                    return $this->redirect(['action' => 'myproperties']);
                } else {
                    $this->Flash->error(__('The property could not be saved. Please, try again.'));
                }
            }
        }
        // Set the layout.
        $this->viewBuilder()->layout('userdash');
        $this->set(compact('property'));
        $this->set('_serialize', ['property']);
    }


    /**
     * @author Norman Lista
     */
    public function boost($id = null)
    {

        $property = $this->Properties->get($id);

		if($property->user_id != $this->Auth->user('id'))
		{

    	    $this->Flash->error(__('You are not the owner of this property'));
    		return $this->redirect(['action' => 'myproperties']);
        } else { 
        if ($this->request->is(['patch', 'post', 'put'])) {

                $property = $this->Properties->patchEntity($property, $this->request->data);
                $now = Time::now();
                $property->boosted_till = $now->addDays(7);
                if ($this->Properties->save($property)) {
                    $this->Flash->success(__('The property ad has been Boosted'));

                    return $this->redirect(['action' => 'myproperties']);
                } else {
                    $this->Flash->error(__('The property could not be boosted. Please, try again.'));
                }

            }
        
        // Set the layout.
        $this->viewBuilder()->layout('userdash');
        $this->set(compact('property'));
        $this->set('_serialize', ['property']);
    }
    }

    

    /**
     * Delete method
     *
     * @param string|null $id Property id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @author Ramanpreet Kaur, Touhidur Rahman
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $property = $this->Properties->get($id);
		
		$session = $this->request->session();
			   
        // only owner user can delete his property
        if ($property->user_id == $this->Auth->user('id') || $session->read('User.admin') == '1'){
            if ($this->Properties->delete($property)) {
                // delete references to this property from favourite properties table
                $favPropTbl = TableRegistry::get('FavoriteProperties');
                $queryFav = $favPropTbl->query();
                $queryFav->delete()->where(['property_id' => $id])->execute();
                // delete references to this property from favourite properties table
                $imgTbl = TableRegistry::get('Images');
                $queryImg = $imgTbl->query();
                $queryImg->delete()->where(['property_id' => $id])->execute();

                $this->Flash->success(__('The property has been deleted.'));
            } else {
                $this->Flash->error(__('The property could not be deleted. Please, try again.'));
            }
        }

		if($session->read('User.admin') != '1')
					return $this->redirect(
        array('controller' => 'users', 'action' => 'dashboard')
		);
		else
		{
		
        		return $this->redirect(
        array('controller' => 'admin', 'action' => 'properties')
		);
		}
    }




    /**
     * Search method
     * URL: http://localhost/studierent/properties/search?address=&type=Student+Residence&min=&max=&score=0&dist=100&directBus=1&avalFrom=20161123&avalTo=20161130&rSize=10&fSize=20&eBillIncl=1&internet=1&cableTv=1&wMachine=1&fireAlarm=1&heating=1&parking=1&bikeParking=1&garden=1&balcony=1&smoking=1&pets=1&rating=5&rating=4&sortby=rentUp
     * @author Touhidur Rahman
     */
    public function search() {
        // access query strings and store in array
        $qs = [];
        $possibleQeries = [
            'address', 'type', 'min', 'max', 'score', 'dist', 'directBus',
            'avalFrom', 'avalTo', 'rSize', 'fSize', 'eBillIncl',
            'internet', 'wMachine', 'fireAlarm', 'heating', 'parking',
            'bikeParking', 'garden', 'balcony', 'smoking', 'pets', 'sortby', 'rating'
        ];
        foreach ($possibleQeries as $key) {
            // if qs is not null only then put inside array
            if ($this->request->query($key)) {
                $qs[$key] = $this->request->query($key);
            }
        }
        // get the query object
        $query = $this->Properties->find();
        // search only in ads that are active [status = 1]
        $query->where(['status' => '1']);
        // if type is supplied, use that
        if ($qs['type']) $query->where(['type' => $qs['type']]);
        if ($qs['address']) {
            $query->where(function($exp){
                // find out zip_id from zips table from the supplied query
                // and use that to lookup in properties table
                $zips = TableRegistry::get('zips')->find()->where(['number' => $this->request->query('address')])->first();
                return $exp->or_([
                    'zip_id' => $zips->id,
                    'address LIKE' => '%'.$this->request->query('address').'%'
                ]);
            });
        }
        // if max rent is given, use also min. (make min 0 if not supplied)
        if ($qs['max']) {
            $query->where(function($exp){
                return $exp
                    ->gte('rent', $this->request->query('min') ? $this->request->query('min') : 0)
                    ->lte('rent', $this->request->query('max'));
            });
        }
        if ($qs['dist'])        $query->where(['dist_from_uni' => $qs['dist']]);
        if ($qs['directBus'])   $query->where(['direct_bus_to_uni' => $qs['directBus']]);
        if ($qs['eBillIncl'])   $query->where(['electricity_bill_included' => $qs['eBillIncl']]);
        if ($qs['internet'])    $query->where(['internet' => $qs['internet']]);
        if ($qs['wMachine'])    $query->where(['washing_machine' => $qs['wMachine']]);
        if ($qs['fireAlarm'])   $query->where(['fire_alarm' => $qs['fireAlarm']]);
        if ($qs['heating'])     $query->where(['heating' => $qs['heating']]);
        if ($qs['parking'])     $query->where(['parking' => $qs['parking']]);
        if ($qs['bikeParking']) $query->where(['bike_parking' => $qs['bikeParking']]);
        if ($qs['garden'])      $query->where(['garden' => $qs['garden']]);
        if ($qs['balcony'])     $query->where(['balcony' => $qs['balcony']]);
        if ($qs['smoking'])     $query->where(['smoking' => $qs['smoking']]);
        if ($qs['pets'])        $query->where(['pets' => $qs['pets']]);
        // search properties in between +5 / -5 room size than the supplied
        if ($qs['rSize']) {
            $query->where(function($exp){
                return $exp->between('room_size', $this->request->query('rSize')-5, $this->request->query('rSize')+5);
            });
        }
        // search properties in between +10 / -10 total size than the supplied
        if ($qs['fSize']) {
            $query->where(function($exp){
                return $exp->between('total_size', $this->request->query('fSize')-10, $this->request->query('fSize')+10);
            });
        }
        // get all properties below availability lower bound
        if ($qs['avalFrom']) {
            $query->where(function($exp){
                return $exp->lte('available_from', new Date($this->request->query('avalFrom')));
            });
        }
        // and above the upper bound
        if ($qs['avalTo']) {
            $query->where(function($exp){
                return $exp->gte('available_until', new Date($this->request->query('avalTo')));
            });
        }
        // search based on landlord rating
        if ($qs['rating']) {
            $query->where(function($exp){
                $avgRatingsTbl = TableRegistry::get('AvgRatings');
                $qualifiedUsersRaw = $avgRatingsTbl->find()
                    ->select(['user_id'])
                    ->where(['avg_rate >=' => $this->request->query('rating')]);
                $qualifiedLandlords = [];
                foreach ($qualifiedUsersRaw as $usr) {
                    $qualifiedLandlords[] = $usr->user_id;
                }
                return $exp->in('user_id', $qualifiedLandlords);
            });
        }
        // prepare sort by
        switch ($qs['sortby']) {
            case 'rentUp':
                $query->order(['rent' => 'DESC']);
                break;
            case 'rentDown':
                $query->order(['rent' => 'ASC']);
                break;
            case 'zipStreet':
                $query->order(['address' => 'ASC', 'zip_id' => 'ASC']);
                break;
            case 'available_to_dt':
                $query->order(['available_to' => 'ASC']);
                break;
            case 'available_from_dt':
                $query->order(['available_from' => 'ASC']);
                break;
            default:
                $query->order(['created' => 'DESC']);
                break;
        }
        // join zips.number field
        $query->contain(['Zips' => function($q){
            return $q->select('number', 'city', 'province');
        }]);
        // // join images table
        $query->contain(['Images']);
        // convert the result set to array
        $properties = $this->paginate($query);
        // count of total retrieved rows
        $count = $query->count();

        // Retrieve landlord avg rating
        $landlords = [];
        foreach ($properties as $k) {
            $landlords[] = $k->user_id;
        }
        $avgRatings = TableRegistry::get('AvgRatings')->find()->where(['user_id IN' => $landlords]);
        // send to view
        $this->set(compact('properties', 'count', 'qs', 'avgRatings'));
        $this->set('_serialize', ['properties']);
    }




    /**
     * Displays User's favorited ads
     * @author Touhidur Rahman
     */
    public function favorites()
    {
		
		$id_exists = true; //to tackle empty properties id
		$favoritesTbl = TableRegistry::get('FavoriteProperties');
        $favAdsCount = $favoritesTbl->find()->select('property_id')->where(['user_id' => $this->Auth->user('id')])->count();	
		
		
		$query = $this->Properties->find();
        $query->where(function($exp){
            $ids = [];
         $favoritesTbl = TableRegistry::get('FavoriteProperties');
         $favAds = $favoritesTbl->find()->select('property_id')->where(['user_id' => $this->Auth->user('id')]);	
		
			foreach ($favAds as $ad) {
                $ids[] = $ad->property_id;
            }
            return $exp->in('Properties.id', $ids);
        });

		
		  // Set the layout.
        $this->viewBuilder()->layout('userdash');
      
	   
		if($favAdsCount > 0 )
		{
			        // join zips.number field
        $query->contain(['Zips' => function($q){
            return $q->select('number', 'city', 'province');
        }]);
        // // join images table
        $query->contain(['Images']);
        $properties = $this->paginate($query);

        // Retrieve landlord avg rating
        $landlords = [];
        foreach ($properties as $k) {
            $landlords[] = $k->user_id;
        }
        $avgRatings = TableRegistry::get('AvgRatings')->find()->where(['user_id IN' => $landlords]);
         //@author Norman Lista
        //send user id for my profile button
        $id= $this->Auth->user('id');
        $this->set(compact('properties','id', 'avgRatings'));
        $this->set('_serialize', ['properties']);

		}
		else 
			$id_exists = false;
		
		$this->set('id_exists',$id_exists);
    }



	/**
	 * Landing page
     * @author Muneeb Noor
	 */
	public function home()
	{

		$recentProperties = $this->Properties
    ->find()
	->contain(['Images'])
    ->order(['created' => 'DESC'])
	->limit(3);

		$boostedProperties = $this->Properties
    ->find()
    ->where(['is_boosted' => 1])
	->contain(['Images'])
    ->order(['created' => 'DESC'])
	->limit(3);

	 $this->set(compact('recentProperties', 'boostedProperties'));
	 $this->set('_serialize', ['recentProperties', 'boostedProperties']);

	}




    /**
     * Displays list of properties a user has posted
     * @author Touhidur Rahman, Norman Lista
     */
    public function myproperties()
    {
        $query = $this->Properties->find()->where(['user_id' => $this->Auth->user('id')]);
        // join zips.number field
        $query->contain(['Zips' => function($q){
            return $q->select('number', 'city', 'province');
        }]);
        // // join images table
        $query->contain(['Images']);
        $properties = $this->paginate($query);
        // Set the layout.
        $this->viewBuilder()->layout('userdash');
        //send user id for my profile button
        $id=$this->Auth->user('id');
        $this->set(compact('properties','id'));
        $this->set('_serialize', ['properties']);
    }




    /**
     * Marks a property as favorite for user
     * URL Pattern: http://localhost/studierent/properties/toggleFavorites.json?id=53
     * @uses Cake\ORM\Entity\FavoriteProperties
     * @author Touhidur Rahman
     */
    public function toggleFavorites()
    {
        $property_id = $this->request->query('id');
        // load FavoriteProperties table
        $favoritesTbl = TableRegistry::get('FavoriteProperties');
        // check if the combination already exists or not
        $query = $favoritesTbl->find()
            ->where(['property_id' => $property_id, 'user_id' => $this->Auth->user('id')]);

        $existsCount = $query->count();
        // if existsCount > 0 remove the combo (user is toggling)
        if ($existsCount > 0) {
            $ret = $favoritesTbl->deleteAll(['property_id' => $property_id, 'user_id' => $this->Auth->user('id')]);
            if ($ret) $data['message'] = 'Deleted';
        } else {
            // insert into db
            $entry = $favoritesTbl->newEntity();
            $entry->property_id = $property_id;
             //@author Norman Lista
             //send user id for my profile button
            $entry->user_id = $this->Auth->user('id');
            $ret = $favoritesTbl->save($entry);
            if ($ret) $data['message'] = 'Added';
        }
        $this->set(compact('data'));
        $this->set('_serialize', ['data']);
    }


}
