<!-- Single Property Ad Snippet -->
<?php
$boosted = false;
if ($property['is_boosted'] == 1) {
    if($property['boosted_till']) {
        if ($property['boosted_till']->isWithinNext(7)) $boosted = true;
    }
}
?>
<div class="card <?= ($boosted)? 'card-outline-warning' : '' ?>">
    <div class="card-block" style="padding-bottom: 0rem">
        <h4 class="card-title">
        <?= ($boosted)? '<i class="fa fa-diamond fa-lg text-warning"></i>' : '' ?>
        <?= $this->Html->link($property['title'], ['action' => 'view', $property['id']], ['target' => '_blank']) ?>
        </h4>
        <h6 class="card-subtitle text-muted">
            <?= $this->Html->link($property['address'], '/properties/search?address='.$property['address'], ['title' => 'Click here to search all properties in '.$property['address']]) ?>
            &nbsp;|&nbsp;
            <?= $this->Html->link($property['zip']['number'], '/properties/search?address='.$property['zip']['number'], ['title' => 'Click here to search all properties in '.$property['zip']['number']]) ?>
        </h6>
    </div>
    <div class="card-block">
        <div class="row">
            <div class="col-sm-3">
                <?php
                if ($property['images']) {
                    echo $this->Html->image('properties'.DS.$property['images'][0]->path, ['alt' => 'Property image', 'class' => 'rounded-left img-fluid', 'style' => 'width:100%;']);
                } else {
                ?>
                    <h1 class="display-1 text-xs-center text-success"><i class="fa fa-home fa-lg"></i></h1>
                <?php
                }
                ?>
            </div>
            <div class="col-sm-6">
                <p class="card-text text-justify">
                    <?= $this->Text->truncate($property['description'], 100, ['exact' => false, 'html' => true]) ?>
                </p>
                <div class="clearfix">
                    <div class="text-success float-xs-left">
                        Studierent Score <i class="fa fa-bolt"></i> <label><strong><?= $property['studierent_score'] ?></strong></label>
                    </div>
                    <div class="text-info float-xs-right">
                        Landlord Rating
                        <?php
                        foreach ($avgRatings as $value) {
                            if ($value->user_id == $property['user_id']) {
                                $filledStar = ceil($value->avg_rate);
                                $hollowStar = 5 - $filledStar;
                                for ($i=0; $i < $filledStar; $i++) {
                                    echo '<i class="fa fa-star"></i>';
                                }
                                for ($i=0; $i < $hollowStar; $i++) {
                                    echo '<i class="fa fa-star-o"></i>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>

                <?= $this->Html->link('<i class="fa fa-home"></i> View Details', '/properties/view/'.$property['id'], ['class' => 'card-link', 'escapeTitle' => false]) ?>
                <?= $this->Html->link('<i class="fa fa-user"></i> Landlord Profile', '/users/view/'.$property['user_id'], ['class' => 'card-link text-info', 'escapeTitle' => false]) ?>
                <?= $this->Html->link('<i class="fa fa-heart-o"></i>', '/properties/toggleFavorites.json?id='.$property['id'], ['class' => 'card-link text-danger favTogglers', 'escapeTitle' => false, 'title' => 'Click to add/remove this property ad to/from your favorites!']) ?>
                <?= $this->Html->link('<i class="fa fa-thumbs-o-up"></i>', '/users/view/'.$property['user_id'].'#sendFeedback', ['class' => 'card-link text-success', 'escapeTitle' => false, 'title' => 'Click to leave a feedback for the landlord so that others find it useful!']) ?>
                <?= $this->Html->link('<i class="fa fa-flag-o"></i>', '/reports/add/'.$property['id'], ['class' => 'card-link text-muted', 'escapeTitle' => false, 'title' => 'Report this Ad! Help us improve Studierent by filtering abuses.']) ?>
            </div>
            <div class="col-sm-3">
                <p class="text-xs-center text-muted" style="margin-bottom:0">EUR</p>
                <h4 class="display-4 text-xs-center text-warning font-weight-bold"><?= $property['rent'] ?></h4>
                <table class="table table-sm table-fluid text-xs-center text-muted">
                    <tr style="font-size:.8rem"><td>Room:</td><td><?= $property['room_size'] ?> M<sup>2</sup></td><td>Total:</td><td><?= $property['total_size'] ?> M<sup>2</sup></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Single Property Ad Snippet end -->
