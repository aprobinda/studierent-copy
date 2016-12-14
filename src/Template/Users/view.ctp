<div class="row">
    <div class="col-xs-12">
	<div class="jumbotron jumbotron-fluid text-xs-center">
	    <div class="container">
                <!-- PHP code
        	@author Mythri Manjunath
	        -->
		<?php
		$path ='users/';
		$filename= $user->photo;
		$userimage=$path.$filename;
		$file = WWW_ROOT . 'img' . DS . 'users' . DS .$filename;
		if(file_exists($file))
		{
           	echo $this->Html->image($userimage, array('alt' => 'User Image','class'=>'rounded-circle','height' => '128', 'width' => '128'));
                }
		else
		echo $this->Html->image('boy.jpg', array('alt' => 'User Image','class'=>'rounded-circle','height' => '128', 'width' => '128'));
		?>
		    <h1 class="display-3"><?= $user->first_name .' '. $user->last_name ?></h1>
				<!-- PHP code
				@author Norman Lista
			-->
	    </div>
        </div>
    </div>

	<div class="col-xs-4">

		<div class="card">
			<div class="card-header">
				Basic Info
			</div>
			<table class="table">
				<tbody>
					<tr>
						<th>Gender</th>
						<td><?= $user->gender ?></td>
					</tr>
					<tr>
						<th>Address</th>
						<td><?= $user->address ?></td>
					</tr>
					<tr>
						<th>Contact No</th>
						<td><?= $user->contact_number ?></td>
					</tr>
					<tr>
						<th>Email</th>
						<td><?= $user->username ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="card">
			<div class="card-header">
				Average Rating
			</div>
			<div class="card-block">

					<?php
						if(!isset($avgRating->avg_rate) ||$avgRating->avg_rate==0){ ?>
                            <h1 class="display-3 text-xs-center text-warning">No rating yet</h1>
				<div class="text-xs-center">
							<p>Not Rated yet</p>
							<?php
                            } else {?>
                                <h1 class="display-3 text-xs-center text-warning"><?= round($avgRating->avg_rate, 2) ?></h1>
				<div class="text-xs-center">
						<?php	$filledStar = ceil($avgRating->avg_rate);
							$hollowStar = 5 - $filledStar;
							for ($i=0; $i < $filledStar; $i++) {
								echo '<i class="fa fa-star text-info"></i>';
							}
							for ($i=0; $i < $hollowStar; $i++) {
								echo '<i class="fa fa-star-o text-info"></i>';
							}
                        }
					?>
				</div>
			</div>
		</div>

		<?php
		//@author Norman Lista
		//for user not rating himself
		if(!($logUser==$user->id)){ ?>
			<div class="card" id="sendFeedback">
				<div class="card-header">
					Send a Feedback
				</div>
				<div class="card-block">

					<?= $this->Form->create($feedback); ?>


					<div class="radio">
						<label style="display:block"><input name="rate" value="5" type="radio">
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</label>
						<label style="display:block"><input name="rate" value="4" type="radio">
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</label>
						<label style="display:block"><input name="rate" value="3" type="radio">
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</label>
						<label style="display:block"><input name="rate" value="2" type="radio">
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</label>
						<label style="display:block"><input name="rate" value="1" type="radio">
							<i class="fa fa-star"></i>
						</label>
					</div>
					<input type="hidden" name="status" value="1" />
					<input type="hidden" name="for_user_id" value="<?= $user->id?>"/>
					<input type="hidden" name="user_id" value="<?= $logUser?>"/>
					<fieldset class="form-group">
						<label for="Select">How do you know this user?</label>
						<select class="form-control" name="relationship_basis" id="relationship_basis">
							<option value="Rented a property from him/her">Rented a property from him/her</option>
							<option value="Contacted, but not rented">Contacted, but not rented</option>
							<option Value="Personal relation">Personal relation</option>
							<option value="Don't know at all">Don't know at all</option>
						</select>
					</fieldset>
					<fieldset class="form-group">
						<label for="text">Details</label>
						<?= $this->Form->Textarea('text',['class' => 'form-control','id' => 'text']);?>
					</fieldset>

					<button type="submit" class="btn btn-success">Submit</button>
					<?= $this->Form->end(); ?>
				</div>
			</div>
		<?php } ?>

	</div>



	<div class="col-xs-8">
		<div class="card">
			<div class="card-header">
				Posted Properties (<?= $propertyCount ?>)
			</div>
			<div class="card-block">
				<div class="card-columns">
					<?php
					foreach ($properties as $property) {
						echo $this->element('adSnippetUserProfileView', ['property' => $property]);
					}
					?>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-header">
				Feedbacks
			</div>
			<div class="card-block">
				<?php
				foreach ($otherFeedbacks as $fdbk) {
					?>
					<h5><?= $this->Html->link($fdbk->user->first_name .' '. $fdbk->user->last_name, ['controller' => 'users', 'action' => 'view', $fdbk->user->id]) ?></h5>
					<p>
						<?php
							$filledStar = $fdbk->rate;
							$hollowStar = 5 - $filledStar;
							for ($i=0; $i < $filledStar; $i++) {
								echo '<i class="fa fa-star text-info"></i>';
							}
							for ($i=0; $i < $hollowStar; $i++) {
								echo '<i class="fa fa-star-o text-info"></i>';
							}
						?>
						<br>
						<?= $fdbk->text ?>
					</p>
					<?php
				}
				?>
			</div>
		</div>

	</div>

</div>
