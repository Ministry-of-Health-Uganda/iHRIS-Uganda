<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn='urn:openhie.org:openinfoman-hwr:stored-function:health_worker_update_operating_hours'>
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="substring-before(/form/@id,'/')"/></xsl:attribute></id>                     
	  <facility>
	    <xsl:attribute name='entityID'><xsl:value-of select="substring-before(substring-after(/form/@id,'/'),'/')"/></xsl:attribute>
	    <service>
	      <xsl:if test="/form/field[@name='csd_service']/value">
		<xsl:attribute name='entityID'><xsl:value-of select="/form/field[@name='csd_service']/value"/></xsl:attribute>
	      </xsl:if>
	      <xsl:attribute name='position'><xsl:value-of select="substring-before(substring-after(substring-after(/form/@id,'/'),'/'),'/')"/></xsl:attribute>
	      <operatingHours>
		<xsl:attribute name='position'><xsl:value-of select="substring-after(substring-after(substring-after(/form/@id,'/'),'/'),'/')"/></xsl:attribute>
		<xsl:if test="/form/field[@name='open']">		
		  <openFlag><xsl:value-of select="/form/field[@name='open']"/></openFlag>
		</xsl:if>
		<xsl:if test="/form/field[@name='day_of_week']/value">		
		  <dayOfTheWeek><xsl:value-of select="/form/field[@name='day_of_week']/value"/></dayOfTheWeek>
		</xsl:if>
		<xsl:if test="/form/field[@name='begin_time']">		
		  <beginningHour><xsl:value-of select="/form/field[@name='begin_time']/hour"/>:<xsl:value-of select="/form/field[@name='begin_time']/minute"/>:<xsl:value-of select="/form/field[@name='begin_time']/second"/></beginningHour>
		</xsl:if>
		<xsl:if test="/form/field[@name='end_time']">		
		  <endingHour><xsl:value-of select="/form/field[@name='end_time']/hour"/>:<xsl:value-of select="/form/field[@name='end_time']/minute"/>:<xsl:value-of select="/form/field[@name='end_time']/second"/></endingHour>
		</xsl:if>
		<xsl:if test="/form/field[@name='begin_date']">		
		  <beginEffectiveDate><xsl:value-of select="/form/field[@name='begin_date']/year"/>-<xsl:value-of select="/form/field[@name='begin_date']/month"/>-<xsl:value-of select="/form/field[@name='begin_date']/day"/></beginEffectiveDate>
		</xsl:if>
		<xsl:if test="/form/field[@name='end_date']">		
		  <endEffectiveDate><xsl:value-of select="/form/field[@name='end_date']/year"/>-<xsl:value-of select="/form/field[@name='end_date']/month"/>-<xsl:value-of select="/form/field[@name='end_date']/day"/></endEffectiveDate>
		</xsl:if>
	      </operatingHours>
	    </service>                                        
	  </facility>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
