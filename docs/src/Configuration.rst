Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for |project|

    +----------+--------+---------------+-------------------------------------+
    | Name     | Type   | Default value | Description                         |
    +==========+========+===============+=====================================+
    | nickserv | string | "nickserv"    | A space-separated list of nicknames |
    |          |        |               | NickServ may use to contact us.     |
    |          |        |               | Usually "nickserv".                 |
    +----------+--------+---------------+-------------------------------------+
    | password | string | n/a           | The password associated with the    |
    |          |        |               | bot's nickname                      |
    +----------+--------+---------------+-------------------------------------+
    | pattern  | string | n/a           | The pattern (regular expression) an |
    |          |        |               | incoming message must match before  |
    |          |        |               | the bot sends out the password on   |
    |          |        |               | the wire.                           |
    +----------+--------+---------------+-------------------------------------+


Example
-------

The following configuration has been used successfully on the
`EpiKnet IRC network`_ which uses ``Themis`` as NickServ's main nickname.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="..."
      language="fr-FR"
      timezone="Europe/Paris"
      commands-prefix="!">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <!-- Configure the bot's nickname, etc. -->
        <module name="Erebot_Module_IrcConnector">
          <param name="nickname" value="Erebot" />
          <param name="identity" value="Erebot" />
          <param name="hostname" value="Erebot" />
          <param name="realname" value="Doh!" />
        </module>
      </modules>

      <networks>
        <network name="EpiKnet">
           <modules>
             <module name="|project|">
               <!--
                 "NickServ" is called "Themis" on EpiKnet.
                 We also allow "NickServ", just in case.
               -->
               <param name="nickserv" value="Themis NickServ" />
               <param name="password" value="********" />

               <!--
                 The pattern is translated in French here because the bot's
                 nickname was configured to use French as its language
                 (and in fact, it's the default language on EpiKnet).
                 You may need to adapt it to your own mileage.
                 Of course, a pattern like ".*(enregistré|registered).*"
                 would also be fine.
               -->
               <param name="pattern"  value=".*enregistré.*" />
             </module>
           </modules>

           <servers>
              <server url="irc://irc.epiknet.org:6667/" />
           </servers>
        </network>
      </networks>
    </configuration>

..  _`EpiKnet IRC network`:
    http://epiknet.org/

.. vim: ts=4 et
