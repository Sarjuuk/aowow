<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1>Удалить учетную запись: <?=$this->username;?></h1>
            <p class="account-delete-box-warning">
            <b>ВНИМАНИЕ!</b> Этот процесс необратим! Если вы решите удалить учетную запись и все связанные с ней персональные данные, то впоследствии мы не сможем ее восстановить.</p>
        <p class="account-delete-box-alternative">
            <b>Если вам требуется помощь в деле удаления каких-либо данных, связанных с учетной записью, а не учетной записи в целом, пожалуйста, <a href="javascript:" onclick="ContactTool.show()">свяжитесь с нами</a>.</b>
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            Мы заботимся о сохранности ваших данных. Если вы хотите воспользоваться «правом на забвение» и запросить удаление ваших персональных данных, пожалуйста, перейдите по указанной ссылке и следуйте инструкциям, представленным на нашем корпоративном сайте:</p>
        <p class="account-delete-box-confirm">
            <input type="submit" name="proceed" value="Продолжить" />
        </p>
    </form>
</div>
