<?php

namespace App\Console\Commands;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateDatabase extends Command
{
    protected $signature = 'db:populate 
                            {--users=100 : Number of users to create}
                            {--translations=100000 : Number of translations to create}
                            {--dry-run : Preview what will be created without seeding}';

    protected $description = 'Populate database with test data for scalability testing';

    private array $localeCodes = ['en', 'fr', 'es', 'de', 'it', 'pt', 'zh', 'ja', 'ko', 'ar'];
    
    private array $localeNames = [
        'en' => 'English', 'fr' => 'French', 'es' => 'Spanish',
        'de' => 'German', 'it' => 'Italian', 'pt' => 'Portuguese',
        'zh' => 'Chinese', 'ja' => 'Japanese', 'ko' => 'Korean', 'ar' => 'Arabic',
    ];

    private array $tagNames = [
        'mobile', 'web', 'desktop', 'tablet', 'email',
        'push', 'sms', 'api', 'admin', 'user',
        'marketing', 'onboarding', 'settings', 'notification', 'analytics',
    ];

    private array $translations = [
        'en' => [
            'sample.test.data' => 'This is the sample test data',
            'sample.content.one' => 'This is sample content number one',
            'sample.content.two' => 'This is sample content number two',
            'sample.message' => 'This is a sample message for testing',
            'welcome.text' => 'Welcome to the translation service',
            'welcome.message' => 'Welcome message for the application',
            'login.title' => 'Login to your account',
            'login.description' => 'Please enter your credentials to access',
            'button.submit' => 'Submit',
            'button.cancel' => 'Cancel',
            'button.save' => 'Save',
            'button.delete' => 'Delete',
            'button.edit' => 'Edit',
            'button.create' => 'Create',
            'button.update' => 'Update',
            'form.label.name' => 'Name',
            'form.label.email' => 'Email',
            'form.label.password' => 'Password',
            'form.label.phone' => 'Phone',
            'form.label.address' => 'Address',
            'error.not.found' => 'Resource not found',
            'error.invalid.input' => 'Invalid input provided',
            'error.server.error' => 'Server error occurred',
            'success.created' => 'Created successfully',
            'success.updated' => 'Updated successfully',
            'success.deleted' => 'Deleted successfully',
            'menu.home' => 'Home',
            'menu.about' => 'About',
            'menu.contact' => 'Contact',
            'menu.profile' => 'Profile',
            'menu.settings' => 'Settings',
            'header.title' => 'Application Title',
            'header.subtitle' => 'Application Subtitle',
            'footer.copyright' => 'Copyright information',
            'footer.terms' => 'Terms and conditions',
            'validation.required' => 'This field is required',
            'validation.email' => 'Please enter a valid email',
            'validation.min.length' => 'Minimum length requirement not met',
            'pagination.next' => 'Next',
            'pagination.previous' => 'Previous',
            'table.header.name' => 'Name',
            'table.header.status' => 'Status',
            'table.header.date' => 'Date',
            'table.header.actions' => 'Actions',
            'notification.success' => 'Operation completed successfully',
            'notification.error' => 'An error occurred',
            'notification.warning' => 'Warning message',
            'notification.info' => 'Information message',
            'search.placeholder' => 'Search here...',
            'search.no.results' => 'No results found',
            'loading.message' => 'Loading data...',
            'confirm.delete' => 'Are you sure you want to delete?',
            'confirm.yes' => 'Yes',
            'confirm.no' => 'No',
        ],
        'fr' => [
            'sample.test.data' => 'Ceci est les données de test',
            'sample.content.one' => 'Ceci est le contenu示例 numéro un',
            'sample.content.two' => 'Ceci est le contenu numéro deux',
            'sample.message' => 'Ceci est un message de test',
            'welcome.text' => 'Bienvenue dans le service de traduction',
            'welcome.message' => 'Message de bienvenue pour l\'application',
            'login.title' => 'Connectez-vous à votre compte',
            'login.description' => 'Veuillez entrer vos identifiants pour accéder',
            'button.submit' => 'Soumettre',
            'button.cancel' => 'Annuler',
            'button.save' => 'Enregistrer',
            'button.delete' => 'Supprimer',
            'button.edit' => 'Modifier',
            'button.create' => 'Créer',
            'button.update' => 'Mettre à jour',
            'form.label.name' => 'Nom',
            'form.label.email' => 'E-mail',
            'form.label.password' => 'Mot de passe',
            'form.label.phone' => 'Téléphone',
            'form.label.address' => 'Adresse',
            'error.not.found' => 'Ressource non trouvée',
            'error.invalid.input' => 'Entrée invalide fournie',
            'error.server.error' => 'Erreur serveur',
            'success.created' => 'Créé avec succès',
            'success.updated' => 'Mis à jour avec succès',
            'success.deleted' => 'Supprimé avec succès',
            'menu.home' => 'Accueil',
            'menu.about' => 'À propos',
            'menu.contact' => 'Contact',
            'menu.profile' => 'Profil',
            'menu.settings' => 'Paramètres',
            'header.title' => 'Titre de l\'application',
            'header.subtitle' => 'Sous-titre de l\'application',
            'footer.copyright' => 'Informations de droit d\'auteur',
            'footer.terms' => 'Termes et conditions',
            'validation.required' => 'Ce champ est obligatoire',
            'validation.email' => 'Veuillez entrer un e-mail valide',
            'validation.min.length' => 'Exigence de longueur minimale non respectée',
            'pagination.next' => 'Suivant',
            'pagination.previous' => 'Précédent',
            'table.header.name' => 'Nom',
            'table.header.status' => 'Statut',
            'table.header.date' => 'Date',
            'table.header.actions' => 'Actions',
            'notification.success' => 'Opération terminée avec succès',
            'notification.error' => 'Une erreur s\'est produite',
            'notification.warning' => 'Message d\'avertissement',
            'notification.info' => 'Message d\'information',
            'search.placeholder' => 'Rechercher ici...',
            'search.no.results' => 'Aucun résultat trouvé',
            'loading.message' => 'Chargement des données...',
            'confirm.delete' => 'Êtes-vous sûr de vouloir supprimer?',
            'confirm.yes' => 'Oui',
            'confirm.no' => 'Non',
        ],
        'es' => [
            'sample.test.data' => 'Este es los datos de prueba',
            'sample.content.one' => 'Este es el contenido de muestra número uno',
            'sample.content.two' => 'Este es el contenido de muestra número dos',
            'sample.message' => 'Este es un mensaje de prueba',
            'welcome.text' => 'Bienvenido al servicio de traducción',
            'welcome.message' => 'Mensaje de bienvenida para la aplicación',
            'login.title' => 'Inicie sesión en su cuenta',
            'login.description' => 'Por favor ingrese sus credenciales para acceder',
            'button.submit' => 'Enviar',
            'button.cancel' => 'Cancelar',
            'button.save' => 'Guardar',
            'button.delete' => 'Eliminar',
            'button.edit' => 'Editar',
            'button.create' => 'Crear',
            'button.update' => 'Actualizar',
            'form.label.name' => 'Nombre',
            'form.label.email' => 'Correo electrónico',
            'form.label.password' => 'Contraseña',
            'form.label.phone' => 'Teléfono',
            'form.label.address' => 'Dirección',
            'error.not.found' => 'Recurso no encontrado',
            'error.invalid.input' => 'Entrada inválida proporcionada',
            'error.server.error' => 'Error del servidor',
            'success.created' => 'Creado exitosamente',
            'success.updated' => 'Actualizado exitosamente',
            'success.deleted' => 'Eliminado exitosamente',
            'menu.home' => 'Inicio',
            'menu.about' => 'Acerca de',
            'menu.contact' => 'Contacto',
            'menu.profile' => 'Perfil',
            'menu.settings' => 'Configuración',
            'header.title' => 'Título de la aplicación',
            'header.subtitle' => 'Subtítulo de la aplicación',
            'footer.copyright' => 'Información de derechos de autor',
            'footer.terms' => 'Términos y condiciones',
            'validation.required' => 'Este campo es obligatorio',
            'validation.email' => 'Por favor ingrese un correo válido',
            'validation.min.length' => 'No cumple con el requisito de longitud mínima',
            'pagination.next' => 'Siguiente',
            'pagination.previous' => 'Anterior',
            'table.header.name' => 'Nombre',
            'table.header.status' => 'Estado',
            'table.header.date' => 'Fecha',
            'table.header.actions' => 'Acciones',
            'notification.success' => 'Operación completada con éxito',
            'notification.error' => 'Ocurrió un error',
            'notification.warning' => 'Mensaje de advertencia',
            'notification.info' => 'Mensaje informativo',
            'search.placeholder' => 'Buscar aquí...',
            'search.no.results' => 'No se encontraron resultados',
            'loading.message' => 'Cargando datos...',
            'confirm.delete' => '¿Está seguro de que desea eliminar?',
            'confirm.yes' => 'Sí',
            'confirm.no' => 'No',
        ],
        'de' => [
            'sample.test.data' => 'Dies sind die Testdaten',
            'sample.content.one' => 'Dies ist Beispielinhalt Nummer eins',
            'sample.content.two' => 'Dies ist Beispielinhalt Nummer zwei',
            'sample.message' => 'Dies ist eine Testnachricht',
            'welcome.text' => 'Willkommen beim Übersetzungsdienst',
            'welcome.message' => 'Willkommensnachricht für die Anwendung',
            'login.title' => 'Melden Sie sich bei Ihrem Konto an',
            'login.description' => 'Bitte geben Sie Ihre Anmeldedaten ein',
            'button.submit' => 'Absenden',
            'button.cancel' => 'Abbrechen',
            'button.save' => 'Speichern',
            'button.delete' => 'Löschen',
            'button.edit' => 'Bearbeiten',
            'button.create' => 'Erstellen',
            'button.update' => 'Aktualisieren',
            'form.label.name' => 'Name',
            'form.label.email' => 'E-Mail',
            'form.label.password' => 'Passwort',
            'form.label.phone' => 'Telefon',
            'form.label.address' => 'Adresse',
            'error.not.found' => 'Ressource nicht gefunden',
            'error.invalid.input' => 'Ungültige Eingabe',
            'error.server.error' => 'Serverfehler',
            'success.created' => 'Erfolgreich erstellt',
            'success.updated' => 'Erfolgreich aktualisiert',
            'success.deleted' => 'Erfolgreich gelöscht',
            'menu.home' => 'Startseite',
            'menu.about' => 'Über uns',
            'menu.contact' => 'Kontakt',
            'menu.profile' => 'Profil',
            'menu.settings' => 'Einstellungen',
            'header.title' => 'Anwendungstitel',
            'header.subtitle' => 'Anwendungsuntertitel',
            'footer.copyright' => 'Urheberrechtsinformationen',
            'footer.terms' => 'Nutzungsbedingungen',
            'validation.required' => 'Dieses Feld ist erforderlich',
            'validation.email' => 'Bitte geben Sie eine gültige E-Mail ein',
            'validation.min.length' => 'Mindestlängenanforderung nicht erfüllt',
            'pagination.next' => 'Weiter',
            'pagination.previous' => 'Zurück',
            'table.header.name' => 'Name',
            'table.header.status' => 'Status',
            'table.header.date' => 'Datum',
            'table.header.actions' => 'Aktionen',
            'notification.success' => 'Vorgang erfolgreich abgeschlossen',
            'notification.error' => 'Ein Fehler ist aufgetreten',
            'notification.warning' => 'Warnmeldung',
            'notification.info' => 'Informationsmeldung',
            'search.placeholder' => 'Hier suchen...',
            'search.no.results' => 'Keine Ergebnisse gefunden',
            'loading.message' => 'Daten werden geladen...',
            'confirm.delete' => 'Sind Sie sicher, dass Sie löschen möchten?',
            'confirm.yes' => 'Ja',
            'confirm.no' => 'Nein',
        ],
        'it' => [
            'sample.test.data' => 'Questi sono i dati di test',
            'sample.content.one' => 'Questo è il contenuto di esempio numero uno',
            'sample.content.two' => 'Questo è il contenuto di esempio numero due',
            'sample.message' => 'Questo è un messaggio di test',
            'welcome.text' => 'Benvenuto al servizio di traduzione',
            'welcome.message' => 'Messaggio di benvenuto per l\'applicazione',
            'login.title' => 'Accedi al tuo account',
            'login.description' => 'Inserisci le tue credenziali per accedere',
            'button.submit' => 'Invia',
            'button.cancel' => 'Annulla',
            'button.save' => 'Salva',
            'button.delete' => 'Elimina',
            'button.edit' => 'Modifica',
            'button.create' => 'Crea',
            'button.update' => 'Aggiorna',
            'form.label.name' => 'Nome',
            'form.label.email' => 'Email',
            'form.label.password' => 'Password',
            'form.label.phone' => 'Telefono',
            'form.label.address' => 'Indirizzo',
            'error.not.found' => 'Risorsa non trovata',
            'error.invalid.input' => 'Input non valido',
            'error.server.error' => 'Errore del server',
            'success.created' => 'Creato con successo',
            'success.updated' => 'Aggiornato con successo',
            'success.deleted' => 'Eliminato con successo',
            'menu.home' => 'Home',
            'menu.about' => 'Chi siamo',
            'menu.contact' => 'Contatti',
            'menu.profile' => 'Profilo',
            'menu.settings' => 'Impostazioni',
            'header.title' => 'Titolo dell\'applicazione',
            'header.subtitle' => 'Sottotitolo dell\'applicazione',
            'footer.copyright' => 'Informazioni sul copyright',
            'footer.terms' => 'Termini e condizioni',
            'validation.required' => 'Questo campo è obbligatorio',
            'validation.email' => 'Inserisci un email valido',
            'validation.min.length' => 'Requisito di lunghezza minima non soddisfatto',
            'pagination.next' => 'Successivo',
            'pagination.previous' => 'Precedente',
            'table.header.name' => 'Nome',
            'table.header.status' => 'Stato',
            'table.header.date' => 'Data',
            'table.header.actions' => 'Azioni',
            'notification.success' => 'Operazione completata con successo',
            'notification.error' => 'Si è verificato un errore',
            'notification.warning' => 'Messaggio di avviso',
            'notification.info' => 'Messaggio informativo',
            'search.placeholder' => 'Cerca qui...',
            'search.no.results' => 'Nessun risultato trovato',
            'loading.message' => 'Caricamento dati...',
            'confirm.delete' => 'Sei sicuro di voler eliminare?',
            'confirm.yes' => 'Sì',
            'confirm.no' => 'No',
        ],
        'pt' => [
            'sample.test.data' => 'Estes são os dados de teste',
            'sample.content.one' => 'Este é o conteúdo de exemplo número um',
            'sample.content.two' => 'Este é o conteúdo de exemplo número dois',
            'sample.message' => 'Esta é uma mensagem de teste',
            'welcome.text' => 'Bem-vindo ao serviço de tradução',
            'welcome.message' => 'Mensagem de boas-vindas para o aplicativo',
            'login.title' => 'Entrar na sua conta',
            'login.description' => 'Por favor, insira suas credenciais para acessar',
            'button.submit' => 'Enviar',
            'button.cancel' => 'Cancelar',
            'button.save' => 'Salvar',
            'button.delete' => 'Excluir',
            'button.edit' => 'Editar',
            'button.create' => 'Criar',
            'button.update' => 'Atualizar',
            'form.label.name' => 'Nome',
            'form.label.email' => 'E-mail',
            'form.label.password' => 'Senha',
            'form.label.phone' => 'Telefone',
            'form.label.address' => 'Endereço',
            'error.not.found' => 'Recurso não encontrado',
            'error.invalid.input' => 'Entrada inválida fornecida',
            'error.server.error' => 'Erro do servidor',
            'success.created' => 'Criado com sucesso',
            'success.updated' => 'Atualizado com sucesso',
            'success.deleted' => 'Excluído com sucesso',
            'menu.home' => 'Início',
            'menu.about' => 'Sobre',
            'menu.contact' => 'Contato',
            'menu.profile' => 'Perfil',
            'menu.settings' => 'Configurações',
            'header.title' => 'Título do aplicativo',
            'header.subtitle' => 'Subtítulo do aplicativo',
            'footer.copyright' => 'Informações de direitos autorais',
            'footer.terms' => 'Termos e condições',
            'validation.required' => 'Este campo é obrigatório',
            'validation.email' => 'Por favor, insira um e-mail válido',
            'validation.min.length' => 'Requisito de comprimento mínimo não atendido',
            'pagination.next' => 'Próximo',
            'pagination.previous' => 'Anterior',
            'table.header.name' => 'Nome',
            'table.header.status' => 'Status',
            'table.header.date' => 'Data',
            'table.header.actions' => 'Ações',
            'notification.success' => 'Operação concluída com sucesso',
            'notification.error' => 'Ocorreu um erro',
            'notification.warning' => 'Mensagem de aviso',
            'notification.info' => 'Mensagem informativa',
            'search.placeholder' => 'Pesquisar aqui...',
            'search.no.results' => 'Nenhum resultado encontrado',
            'loading.message' => 'Carregando dados...',
            'confirm.delete' => 'Tem certeza de que deseja excluir?',
            'confirm.yes' => 'Sim',
            'confirm.no' => 'Não',
        ],
        'zh' => [
            'sample.test.data' => '这是示例测试数据',
            'sample.content.one' => '这是示例内容编号一',
            'sample.content.two' => '这是示例内容编号二',
            'sample.message' => '这是一条测试消息',
            'welcome.text' => '欢迎使用翻译服务',
            'welcome.message' => '应用程序的欢迎消息',
            'login.title' => '登录您的账户',
            'login.description' => '请输入您的凭据以访问',
            'button.submit' => '提交',
            'button.cancel' => '取消',
            'button.save' => '保存',
            'button.delete' => '删除',
            'button.edit' => '编辑',
            'button.create' => '创建',
            'button.update' => '更新',
            'form.label.name' => '姓名',
            'form.label.email' => '电子邮件',
            'form.label.password' => '密码',
            'form.label.phone' => '电话',
            'form.label.address' => '地址',
            'error.not.found' => '未找到资源',
            'error.invalid.input' => '提供了无效的输入',
            'error.server.error' => '服务器错误',
            'success.created' => '创建成功',
            'success.updated' => '更新成功',
            'success.deleted' => '删除成功',
            'menu.home' => '首页',
            'menu.about' => '关于',
            'menu.contact' => '联系',
            'menu.profile' => '个人资料',
            'menu.settings' => '设置',
            'header.title' => '应用标题',
            'header.subtitle' => '应用副标题',
            'footer.copyright' => '版权信息',
            'footer.terms' => '条款和条件',
            'validation.required' => '此字段为必填项',
            'validation.email' => '请输入有效的电子邮件',
            'validation.min.length' => '未满足最小长度要求',
            'pagination.next' => '下一页',
            'pagination.previous' => '上一页',
            'table.header.name' => '名称',
            'table.header.status' => '状态',
            'table.header.date' => '日期',
            'table.header.actions' => '操作',
            'notification.success' => '操作成功完成',
            'notification.error' => '发生错误',
            'notification.warning' => '警告消息',
            'notification.info' => '信息消息',
            'search.placeholder' => '在此搜索...',
            'search.no.results' => '未找到结果',
            'loading.message' => '正在加载数据...',
            'confirm.delete' => '您确定要删除吗？',
            'confirm.yes' => '是',
            'confirm.no' => '否',
        ],
        'ja' => [
            'sample.test.data' => 'これはサンプルテストデータです',
            'sample.content.one' => 'これはサンプルコンテンツ番号1です',
            'sample.content.two' => 'これはサンプルコンテンツ番号2です',
            'sample.message' => 'これはテストメッセージです',
            'welcome.text' => '翻訳サービスへようこそ',
            'welcome.message' => 'アプリケーションへようこそメッセージ',
            'login.title' => 'アカウントにログイン',
            'login.description' => 'アクセスするには認証情報を入力してください',
            'button.submit' => '送信',
            'button.cancel' => 'キャンセル',
            'button.save' => '保存',
            'button.delete' => '削除',
            'button.edit' => '編集',
            'button.create' => '作成',
            'button.update' => '更新',
            'form.label.name' => '名前',
            'form.label.email' => 'メール',
            'form.label.password' => 'パスワード',
            'form.label.phone' => '電話番号',
            'form.label.address' => '住所',
            'error.not.found' => 'リソースが見つかりません',
            'error.invalid.input' => '無効な入力',
            'error.server.error' => 'サーバーエラー',
            'success.created' => '正常に作成されました',
            'success.updated' => '正常に更新されました',
            'success.deleted' => '正常に削除されました',
            'menu.home' => 'ホーム',
            'menu.about' => 'について',
            'menu.contact' => 'お問い合わせ',
            'menu.profile' => 'プロフィール',
            'menu.settings' => '設定',
            'header.title' => 'アプリケーションのタイトル',
            'header.subtitle' => 'アプリケーションのサブタイトル',
            'footer.copyright' => '著作権情報',
            'footer.terms' => '利用規約',
            'validation.required' => 'この項目は必須です',
            'validation.email' => '有効なメールアドレスを入力してください',
            'validation.min.length' => '最小文字数の要件を満たしていません',
            'pagination.next' => '次へ',
            'pagination.previous' => '前へ',
            'table.header.name' => '名前',
            'table.header.status' => 'ステータス',
            'table.header.date' => '日付',
            'table.header.actions' => 'アクション',
            'notification.success' => '操作が正常に完了しました',
            'notification.error' => 'エラーが発生しました',
            'notification.warning' => '警告メッセージ',
            'notification.info' => '情報メッセージ',
            'search.placeholder' => 'ここに検索...',
            'search.no.results' => '結果が見つかりません',
            'loading.message' => 'データを読み込み中...',
            'confirm.delete' => '削除してもよろしいですか？',
            'confirm.yes' => 'はい',
            'confirm.no' => 'いいえ',
        ],
        'ko' => [
            'sample.test.data' => '이것은 샘플 테스트 데이터입니다',
            'sample.content.one' => '이것은 샘플 콘텐츠 번호 하나입니다',
            'sample.content.two' => '이것은 샘플 콘텐츠 번호 둘입니다',
            'sample.message' => '이것은 테스트 메시지입니다',
            'welcome.text' => '번역 서비스에 오신 것을 환영합니다',
            'welcome.message' => '애플리케이션에 대한 환영 메시지',
            'login.title' => '계정에 로그인',
            'login.description' => '액세스하려면 자격 증명을 입력하세요',
            'button.submit' => '제출',
            'button.cancel' => '취소',
            'button.save' => '저장',
            'button.delete' => '삭제',
            'button.edit' => '편집',
            'button.create' => '생성',
            'button.update' => '업데이트',
            'form.label.name' => '이름',
            'form.label.email' => '이메일',
            'form.label.password' => '비밀번호',
            'form.label.phone' => '전화',
            'form.label.address' => '주소',
            'error.not.found' => '리소스를 찾을 수 없음',
            'error.invalid.input' => '잘못된 입력',
            'error.server.error' => '서버 오류',
            'success.created' => '성공적으로 생성됨',
            'success.updated' => '성공적으로 업데이트됨',
            'success.deleted' => '성공적으로 삭제됨',
            'menu.home' => '홈',
            'menu.about' => '정보',
            'menu.contact' => '연락처',
            'menu.profile' => '프로필',
            'menu.settings' => '설정',
            'header.title' => '애플리케이션 제목',
            'header.subtitle' => '애플리케이션 부제',
            'footer.copyright' => '저작권 정보',
            'footer.terms' => '이용 약관',
            'validation.required' => '이 필드는 필수입니다',
            'validation.email' => '유효한 이메일을 입력하세요',
            'validation.min.length' => '최소 길이 요구사항을 충족하지 않음',
            'pagination.next' => '다음',
            'pagination.previous' => '이전',
            'table.header.name' => '이름',
            'table.header.status' => '상태',
            'table.header.date' => '날짜',
            'table.header.actions' => '작업',
            'notification.success' => '작업이 성공적으로 완료되었습니다',
            'notification.error' => '오류가 발생했습니다',
            'notification.warning' => '경고 메시지',
            'notification.info' => '정보 메시지',
            'search.placeholder' => '여기에서 검색...',
            'search.no.results' => '결과를 찾을 수 없습니다',
            'loading.message' => '데이터 로드 중...',
            'confirm.delete' => '삭제하시겠습니까?',
            'confirm.yes' => '예',
            'confirm.no' => '아니오',
        ],
        'ar' => [
            'sample.test.data' => 'هذا هو بيانات الاختبار النموذجية',
            'sample.content.one' => 'هذا هو المحتوى النموذجي رقم واحد',
            'sample.content.two' => 'هذا هو المحتوى النموذجي رقم اثنين',
            'sample.message' => 'هذه رسالة اختبار',
            'welcome.text' => 'مرحباً بك في خدمة الترجمة',
            'welcome.message' => 'رسالة ترحيب للتطبيق',
            'login.title' => 'تسجيل الدخول إلى حسابك',
            'login.description' => 'يرجى إدخال بيانات اعتماد للوصول',
            'button.submit' => 'إرسال',
            'button.cancel' => 'إلغاء',
            'button.save' => 'حفظ',
            'button.delete' => 'حذف',
            'button.edit' => 'تحرير',
            'button.create' => 'إنشاء',
            'button.update' => 'تحديث',
            'form.label.name' => 'الاسم',
            'form.label.email' => 'البريد الإلكتروني',
            'form.label.password' => 'كلمة المرور',
            'form.label.phone' => 'الهاتف',
            'form.label.address' => 'العنوان',
            'error.not.found' => 'المورد غير موجود',
            'error.invalid.input' => 'إدخال غير صالح',
            'error.server.error' => 'خطأ في الخادم',
            'success.created' => 'تم الإنشاء بنجاح',
            'success.updated' => 'تم التحديث بنجاح',
            'success.deleted' => 'تم الحذف بنجاح',
            'menu.home' => 'الرئيسية',
            'menu.about' => 'حول',
            'menu.contact' => 'اتصل بنا',
            'menu.profile' => 'الملف الشخصي',
            'menu.settings' => 'الإعدادات',
            'header.title' => 'عنوان التطبيق',
            'header.subtitle' => 'العنوان الفرعي للتطبيق',
            'footer.copyright' => 'معلومات حقوق النشر',
            'footer.terms' => 'الشروط والأحكام',
            'validation.required' => 'هذا الحقل مطلوب',
            'validation.email' => 'يرجى إدخال بريد إلكتروني صالح',
            'validation.min.length' => 'لم يتم استيفاء متطلب الحد الأدنى للطول',
            'pagination.next' => 'التالي',
            'pagination.previous' => 'السابق',
            'table.header.name' => 'الاسم',
            'table.header.status' => 'الحالة',
            'table.header.date' => 'التاريخ',
            'table.header.actions' => 'الإجراءات',
            'notification.success' => 'تمت العملية بنجاح',
            'notification.error' => 'حدث خطأ',
            'notification.warning' => 'رسالة تحذير',
            'notification.info' => 'رسالة معلومات',
            'search.placeholder' => 'ابحث هنا...',
            'search.no.results' => 'لم يتم العثور على نتائج',
            'loading.message' => 'جاري تحميل البيانات...',
            'confirm.delete' => 'هل أنت متأكد أنك تريد الحذف؟',
            'confirm.yes' => 'نعم',
            'confirm.no' => 'لا',
        ],
    ];

    private int $baseKeysCount;

    public function handle(): int
    {
        $this->baseKeysCount = count($this->translations['en']);
        
        $usersCount = (int) $this->option('users');
        $translationsCount = (int) $this->option('translations');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No data will be created');
            $this->line('');
            $this->info("Will create:");
            $this->line("  - {$usersCount} users");
            $this->line("  - " . count($this->localeCodes) . " locales");
            $this->line("  - " . count($this->tagNames) . " tags");
            $this->line("  - {$translationsCount} translations ({$this->baseKeysCount} base keys × variations per locale)");
            return Command::SUCCESS;
        }

        $this->info('Starting database population...');
        $this->line('');

        $startTime = microtime(true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->createUsers($usersCount);
        $this->createLocales();
        $this->createTags();
        $this->createTranslations($translationsCount);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->line('');
        $this->info("Database population completed in {$duration} seconds!");
        $this->line('');
        $this->info('Summary:');
        $this->line("  - Users: " . User::count());
        $this->line("  - Locales: " . Locale::count());
        $this->line("  - Tags: " . Tag::count());
        $this->line("  - Translations: " . Translation::count());

        return Command::SUCCESS;
    }

    private function createUsers(int $count): void
    {
        $this->info("Creating {$count} users...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $chunks = array_chunk(range(1, $count), 1000);

        foreach ($chunks as $chunk) {
            $users = [];
            foreach ($chunk as $i) {
                $users[] = [
                    'name' => "User {$i}",
                    'email' => "user{$i}@example.com",
                    'password' => bcrypt('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('users')->insert($users);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->line(' ✓');
    }

    private function createLocales(): void
    {
        $this->info('Creating locales...');

        $locales = [];

        foreach ($this->localeCodes as $code) {
            $locales[] = [
                'code' => $code,
                'name' => $this->localeNames[$code] ?? ucfirst($code),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('locales')->insert($locales);
        $this->line(' ✓ (' . count($locales) . ' locales)');
    }

    private function createTags(): void
    {
        $this->info('Creating tags...');

        $tags = [];
        foreach ($this->tagNames as $name) {
            $tags[] = [
                'name' => $name,
                'description' => "Translations for {$name} context",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('tags')->insert($tags);
        $this->line(' ✓ (' . count($tags) . ' tags)');
    }

    private function createTranslations(int $count): void
    {
        $this->info("Creating {$count} translations...");

        $locales = Locale::all();
        $translationsPerLocale = $count / count($locales);
        $variationsPerKey = (int) ceil($translationsPerLocale / $this->baseKeysCount);

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $batchSize = 5000;

        foreach ($locales as $locale) {
            $localeCode = $locale->code;
            $localeTranslations = $this->translations[$localeCode] ?? $this->translations['en'];
            
            $translationIndex = 1;

            for ($variation = 1; $variation <= $variationsPerKey; $variation++) {
                $batch = [];

                foreach ($localeTranslations as $key => $content) {
                    if (count($batch) >= $batchSize) {
                        break;
                    }

                    $fullKey = "{$key}.{$variation}";
                    $variedContent = $this->generateVariedContent($content, $variation);
                    
                    $batch[] = [
                        'locale_id' => $locale->id,
                        'key' => $fullKey,
                        'content' => $variedContent,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $translationIndex++;
                }

                if (empty($batch)) {
                    break;
                }

                DB::table('translations')->insert($batch);

                $startId = Translation::max('id') - count($batch) + 1;
                $endId = Translation::max('id');

                $this->attachRandomTagsInBatches($startId, $endId);

                $bar->advance(count($batch));
            }
        }

        $bar->finish();
        $this->line(' ✓');
    }

    private function generateVariedContent(string $content, int $variation): string
    {
        $variations = [
            " [Variation {$variation}]",
            " (ID: {$variation})",
            " #{$variation}",
            " - {$variation}",
            " v.{$variation}",
        ];

        $suffix = $variations[array_rand($variations)];
        
        return $content . $suffix;
    }

    private function attachRandomTagsInBatches(int $startId, int $endId): void
    {
        $tags = Tag::all();
        $tagIds = $tags->pluck('id')->toArray();

        $chunkSize = 500;

        for ($id = $startId; $id <= $endId; $id += $chunkSize) {
            $batchEnd = min($id + $chunkSize - 1, $endId);
            $pivotData = [];

            for ($i = $id; $i <= $batchEnd; $i++) {
                $numTags = rand(1, count($tagIds));
                $selectedTagIds = array_rand(array_flip($tagIds), $numTags);

                if (!is_array($selectedTagIds)) {
                    $selectedTagIds = [$selectedTagIds];
                }

                foreach ($selectedTagIds as $tagId) {
                    $pivotData[] = [
                        'translation_id' => $i,
                        'tag_id' => $tagId,
                    ];
                }
            }

            DB::table('translation_tag')->insert($pivotData);
        }
    }
}